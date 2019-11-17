require 'yaml'

run_locally do
  config = YAML.load(File.read("../../../../../extra-config.yaml"))
  acf_key = config["acf_pro_key"]
  master_db_password = config["master_db_password"]
  set :acf_key, acf_key
  set :master_db_password, master_db_password
end

fetch(:default_env).merge!({
  'ACF_PRO_KEY' => fetch(:acf_key),
})

namespace :setup do
  desc "ask for database password"
  task 'ask_db_password' do
    set :db_password, ask( "the database password for #{fetch(:stage)}", 'password' )
    fetch :db_password
  end

  desc "get database password out of .env file"
  task 'get_db_password' do
    on roles(:app) do
      within "/var/www/vhosts/#{fetch(:application)}/staging/shared" do
        pass = capture :wp, "dotenv get DB_PASSWORD"
        set :db_password, pass
        puts "got DB password: #{fetch(:db_password)}"
      end
    end
  end

  desc "check that .env file exists"
  task 'check_server_setup' do |t|
    on roles(:app) do
      if test("[ -f #{shared_path}/.env ]")
        puts "dot env file exists"
      else
        puts "dot env file doesnt exist"
        execute :touch, "#{shared_path}/.env"
      end
    end
  end

  desc "pull down everything in the /uploads directory on the server to local dev"
  task :pull_uploads do
    run_locally do
      roles(:app).each do |host|
        execute "rsync -rv --progress --ignore-existing #{host.user}@#{host.hostname}:#{shared_path}/web/app/uploads/ #{fetch(:local_path)}web/app/uploads/"
      end
    end
  end

  desc "copy the database to local dev using wp cli migratedb export"
  task :pull_db do |t|
    sql_filename = "#{fetch(:application)}_#{fetch(:stage)}.sql"
    find_str = "//#{fetch(:nginx_domain)}"
    replace_str = "//#{fetch(:application)}.sockman.test"
    on roles(:app) do
      within release_path do
        execute :wp, "migratedb export /tmp/#{sql_filename} --find=#{find_str} --replace=#{replace_str}"
        download! "/tmp/#{sql_filename}", "#{sql_filename}"
      end
    end
    run_locally do
      execute :vagrant, "ssh -c 'mysql -u root --password=root #{fetch(:application)} < /srv/www/#{fetch(:application)}/site/#{fetch(:application)}/#{sql_filename}'"
      execute :rm, sql_filename
    end
  end

  desc "create .env file"
  task 'setup_dot_env_file' do |t|
    on roles(:app) do
      within release_path do
        execute :wp, "dotenv init --with-salts --force"
        execute :wp, "dotenv set DB_NAME #{fetch(:db_name)}"
        execute :wp, "dotenv set DB_USER #{fetch(:db_user)}"
        execute :wp, "dotenv set DB_PASSWORD #{fetch(:db_password)}"
        execute :wp, "dotenv set WP_ENV #{fetch(:stage)}"
        execute :wp, "dotenv set WP_HOME #{fetch(:wp_home)}"
        # TODO: this line below doesnt include ${WP_HOME} for some reason it gets lost.
        execute :wp, "dotenv set WP_SITEURL ${WP_HOME}/wp"
        execute :wp, "dotenv set ACF_PRO_KEY #{fetch(:acf_key)}"
      end
    end
  end

  desc "setup nginx conf and symlink it"
  task :setup_nginx do |t|
    after 'nginx:site:add', 'nginx:site:enable'
    after 'nginx:site:enable', 'nginx:reload'
    invoke 'nginx:site:add'
  end

  desc "remove nginx conf and unsymlink it"
  task :remove_nginx do |t|
    after 'nginx:site:disable', 'nginx:site:remove'
    after 'nginx:site:remove', 'nginx:reload'
    invoke 'nginx:site:disable'
  end

  desc "fix the permissions on the uploads directory"
  task :fix_uploads_permissions do |t|
    on roles(:app) do
      execute :chgrp, "-R dalemartyn #{shared_path}/web/app/uploads"
      execute :chmod, "-R g+w #{shared_path}/web/app/uploads"
    end
  end

  desc "export the database from local using wp cli migratedb export"
  task :upload_local_db do |t|
    sql_filename = "#{fetch(:application)}_#{fetch(:stage)}.sql"
    find_str = "//#{fetch(:application)}.sockman.test"
    replace_str = "//#{fetch(:nginx_domain)}"
    run_locally do
      execute :vagrant, "ssh -c 'cd /srv/www/#{fetch(:application)}/site/#{fetch(:application)} && wp migratedb export #{sql_filename} --find=#{find_str} --replace=#{replace_str}'"
    end
    on roles(:db) do
      upload! sql_filename, "/tmp/#{sql_filename}"
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' #{fetch(:db_name)} < /tmp/#{sql_filename}"
    end
    run_locally do
      execute :rm, sql_filename
    end
  end

  desc "copy everything in the /uploads directory from local"
  task :copy_local_uploads do
    local_uploads_dir = "web/app/uploads"
    on roles(:app) do
      upload! "#{local_uploads_dir}", "#{shared_path}/web/app/", recursive: true
    end
  end

  desc "sync everything in the /uploads directory from local"
  task :sync_local_uploads do
    run_locally do
      roles(:app).each do |host|
        execute "rsync -rv --progress --ignore-existing #{fetch(:local_path)}/web/app/uploads/ #{host.user}@#{host.hostname}:#{shared_path}/web/app/uploads/"
      end
    end
  end

  desc "copy the staging database to current stage using wp cli migratedb export"
  task :copy_staging_db do |t|
    sql_filename = "#{fetch(:application)}_#{fetch(:stage)}.sql"
    find_str = "//#{fetch(:application)}.dalemartyn.co.uk"
    replace_str = "//#{fetch(:nginx_domain)}"
    on roles(:app) do
      within "/var/www/vhosts/#{fetch(:application)}/staging/current" do
        execute :wp, "migratedb export /tmp/#{sql_filename} --find=#{find_str} --replace=#{replace_str}"
      end
    end
    on roles(:db) do
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' #{fetch(:db_name)} < /tmp/#{sql_filename}"
    end
  end

  desc "copy everything in the /uploads directory from staging"
  task :copy_staging_uploads do
    staging_uploads_dir = "/var/www/vhosts/#{fetch(:application)}/staging/shared/web/app/uploads"
    on roles(:app) do
      execute :cp, "-rp #{staging_uploads_dir}/. #{release_path}/web/app/uploads/"
    end
  end

  desc "create database user"
  task :create_db_user do |t|
    on roles(:db) do
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' -e \"CREATE USER '#{fetch(:db_user)}'@'localhost' IDENTIFIED BY '#{fetch(:db_password)}'\""
    end
  end

  desc "drop database user"
  task :drop_db_user do |t|
    on roles(:db) do
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' -e \"DROP USER IF EXISTS '#{fetch(:db_user)}'@'localhost'\""
    end
  end

  desc "create database"
  task :create_db do |t|
    on roles(:db) do
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' -e \"CREATE DATABASE IF NOT EXISTS #{fetch(:db_name)}\""
      execute :mysql, "-u root --password='#{fetch(:master_db_password)}' -e \"GRANT ALL PRIVILEGES ON  #{fetch(:db_name)}.* TO #{fetch(:db_user)}@localhost;\""
    end
  end

  task :default do
    # hook one of:
    #   - 'setup:ask_for_db_password'
    #   - 'setup:get_db_password'
    # in stage specific config.

    # variable to test if in setup (e.g. to stop purge cache.)
    set :setup, true
    before 'deploy:check:linked_files', 'setup:check_server_setup'
    after 'composer:install', 'setup:setup_dot_env_file'
    after 'deploy:started', 'setup:setup_nginx'
    after 'deploy', 'setup:fix_uploads_permissions'
    after 'setup:setup_nginx', 'setup:create_db'

    invoke 'deploy'
  end

end

task :setup => 'setup:default'

task :sync_data do
  after 'setup:upload_local_db', 'setup:sync_local_uploads'
  invoke 'setup:upload_local_db'
end

task :sync_all do
  after 'deploy', 'sync_data'
  invoke 'deploy'
end
