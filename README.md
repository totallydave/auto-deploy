# What is AutoDeploy?
AutoDeploy is a *Zend Framework 2*-Module which provides a service to auto deploy code utilising web hooks from VCS providers.

##Available services
- Vcs (Git)
- Dm - Dependency Manager (Composer)
- Db - Database Migrations (Executing MySql migration file/s)

###Notes 
- Dm and Db services depend on a Vcs service
- Db service will be run last
- Db migration files must begin with '\_auto_deploy\_' this allows them to be ommitted from auto deployment
- An error on the Db service will roll the other services back so it's important Vcs is run first (@todo force this)
- An email log will be sent to any commiters in the update
- If logging is enables a log will be written to the file system

## Installation

```
composer require totallydave/auto-deploy
```

Add the below to your index.php or web root php file
```
define('APPLICATION_ROOT', realpath(dirname(__DIR__)));
```

Create a auto_deploy.php and place in config/autoload by running the below
```
cp vendor/totallydave/auto-deploy/config/module.config.php config/autoload/auto_deploy.php
```

Update the default configuration as you need (below is the minimum you'll need to get up and running)
```
 ...

 'application' => [
     'email' => [
         'allowedDevEmailDomains' => [
             [YOUR EMAIL DOMAIN] // use regex pattern e.g. 'github\.com' for foo@github.com
         ],
     ]
 ],

  ...

 'auto_deploy' => [
     'services' => [
         'vcs' => [
             'originUrl' => '[GIT REPOSITORY URL]' // This is the git branch that you wish to auto deploy
         ],

         ...

         'dm' => [
             'name' => '[COMPOSER PACKAGE NAME]' // This is required to identify the correct composer.json file
         ],

        ...

         'db' => [
             'type' => 'mysql',

              // This is where the db migration files are kept relative to the vcs root
              // migration files will only be applied if they start with '_auto_deploy_' - this is
              // to allow you the flexibility to pick and choose when auto db migration is applied
              // as it is not advised to use this feature for any heavy lifting
             'migrationDir' => '[MIGRATION FILE DIRECTORY]',

             // This is where the backup taken prior to a db migration are kept relative to the vcs root
             // make sure this directory is excluded from version control
             'backupDir' => '[MIGRATION BACKUP DIRECTORY]',

             // the below is required to perform db migrations - this is the target db
             'connection' => [
                 'hostname' => '[DB HOST]',
                 'username' => '[DB USERNAME]',
                 'password' => '[DB PASSWORD]',
                 'database' => '[DB NAME]'
             ],
             // the below is required to perform db migrations - multiple databases can be backed up if required
             'backup_connections' => [[
                 'hostname' => '[DB HOST]',
                 'username' => '[DB USERNAME]',
                 'password' => '[DB PASSWORD]',
                 'database' => '[DB NAME]'
             ]],
         ],

         ...
     ],

      // This is a list of white-listed IP addresses for the modules internal firewall
     'ipAddresses' => [
          [GIT SERVER PROVIDER IP]
     ],
  ]

 ...
```

Add 'AutoDeploy' to your registered modules in application.config.php
```
return array(
    'modules' => array(
        // other modules
        ...

        'AutoDeploy'

        ...
    ),
    // other content
);

```

Configure web hook in your chosen vcs provider to call [YOUR APPLICATION URL]/auto_deploy/ on push event

## Prerequisites
- composer must be available globally by using command 'composer' to user composer service
- mysql-server must be installed to use mysql db service
- git must be installed to user git vcs service

## GOTCHAS
- auto_deploy.ipAddress : IP of VCS server must be added in config [GIT SERVER PROVIDER IP]
- application.email : correctly configure this or you might find the emails end up in your spam
- application.email.allowedDevEmailDomains : this relies on php environment variable 'env'
- auto_deploy.services.db : exclude backupDir from vcs
- auto_deploy.services.db : migration depends on vcs
- auto_deploy.services.db : migration files must start with '\_auto_deploy\_'

## @todo
- add rollback for other failures
- write rollback for db
- allow multiple services within each type
- write unit tests
