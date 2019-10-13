require_relative 'nginx'
require_relative 'setup'

set :application, 'daleimg'
set :repo_url, 'git@bitbucket.org:adozeneggs/daleimg_wordpress.git'


set :ssh_options, {
  #keys: %w(~/.ssh/id_rsa),
  forward_agent: true
  #auth_methods: %w(password)
}

# Use :debug for more verbose output when troubleshooting
set :log_level, :info

# Apache users with .htaccess files:
# it needs to be added to linked_files so it persists across deploys:
set :linked_files, fetch(:linked_files, []).push('.env')
set :linked_dirs, fetch(:linked_dirs, []).push('web/app/uploads')

set :nvm_type, :user # or :system, depends on your nvm setup
set :nvm_node, 'v8.16.0'
set :nvm_map_bins, %w{node npm yarn}

# for setup task
set :db_user, -> { "#{fetch(:application)}" }

namespace :deploy do
  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      # Your restart mechanism here, for example:
      # execute :service, :nginx, :reload
    end
  end
end

# The above restart task is not run by default
# Uncomment the following line to run it on deploys if needed
# after 'deploy:publishing', 'deploy:restart'

namespace :deploy do
  desc 'Update WordPress template root paths to point to the new release'
  task :update_option_paths do
    on roles(:app) do
      within fetch(:release_path) do
        if test :wp, :core, 'is-installed'
          [:stylesheet_root, :template_root].each do |option|
            # Only change the value if it's an absolute path
            # i.e. The relative path "/themes" must remain unchanged
            # Also, the option might not be set, in which case we leave it like that
            value = capture :wp, :option, :get, option, raise_on_non_zero_exit: false
            if value != '' && value != '/themes'
              execute :wp, :option, :set, option, fetch(:release_path).join('web/wp/wp-content/themes')
            end
          end
        end
      end
    end
  end

  desc 'Build the assets'
  task :build_assets do
    on roles(:app) do
      within fetch(:release_path) do
        execute :npm, :install
        execute :npm, :run, :install
        execute :npm, :run, :build
      end
    end
  end

  desc 'Purge Nginx Cache'
  task :purge_cache do
    unless fetch(:setup)
      on roles(:app), in: :sequence, wait: 5 do
        within fetch(:release_path) do
          execute! :sudo, "-u www-data wp eval '$ngc = new NginxCache(); $ngc->purge_zone_once();' "
        end
      end
    end
  end
end

set :manual_plugins, [ ]
set :local_path, "~/WordPressSites/daleimg/"

# upload woocommerce-paypal-pro plugin
namespace :deploy do
  desc "copy up non-composer plugins"
  task :upload_manual_plugins do
    run_locally do
      fetch(:manual_plugins).each do |p|
        roles(:app).each do |host|
          execute "rsync -rv --progress --chmod=0775 --ignore-existing #{fetch(:local_path)}/web/app/plugins/#{p} #{host.user}@#{host.hostname}:#{fetch(:release_path)}/web/app/plugins/"
        end
      end
    end
  end
end

# The above update_option_paths task is not run by default
# Note that you need to have WP-CLI installed on your server
# Uncomment the following line to run it on deploys if needed
after 'deploy:updated', 'deploy:update_option_paths'
after 'deploy:updated', 'deploy:build_assets'
# after 'deploy:published', 'deploy:upload_manual_plugins'
