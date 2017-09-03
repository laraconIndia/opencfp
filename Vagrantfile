VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # box-config
  config.vm.box = "DreiWolt/devops007"

  # network-config
  config.vm.network :private_network, ip: "192.168.3.23"
  config.vm.hostname = "OpenCFP"
  config.vm.synced_folder ".", "/vagrant", type: "nfs"
  config.hostsupdater.aliases = ["cfp-dev.phpdd.org", "pma.phpdd.org", "readis.phpdd.org"]

  # provisioning
  config.vm.provision "file", source: "env/vagrant/id_rsa", destination: "/home/vagrant/.ssh/id_rsa"
  config.vm.provision "file", source: "env/vagrant/ssh_config", destination: "/home/vagrant/.ssh/config"
  config.vm.provision "shell", path: "env/vagrant/bootstrap.sh"

end
