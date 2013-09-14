require File.expand_path('../support/helpers', __FILE__)

describe 'nginx::source' do
  include Helpers::Nginx

  it 'runs a service named nginx' do
    service("nginx").must_be_running
  end
end
