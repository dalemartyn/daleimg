set :stage, :production

set :branch, ask( 'Branch to deploy?', 'master' )

set :deploy_to, -> { "/var/www/vhosts/#{fetch(:application)}/#{fetch(:stage)}" }

set :keep_releases, 3

# update to https / correct URL.
set :wp_home, -> { "http://img.dalestillman.com" }
set :db_name, -> { "#{fetch(:application)}_production" }

set :nginx_domain, -> { "img.dalestillman.com" }





# Extended Server Syntax
# ======================
server 'dalwhinnie', roles: %w{web app db}, user: 'adozeneggs'





fetch(:default_env).merge!(wp_env: :production)


before 'setup:default', 'setup:ask_db_password'
before 'setup:setup_dot_env_file', 'setup:ask_db_password'
before 'setup:create_db', 'setup:create_db_user'
after 'setup:create_db', 'setup:upload_local_db'
after 'setup:create_db', 'setup:copy_local_uploads'
