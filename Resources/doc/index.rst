A bundle to add maintenance mode to Symfony project.

Installation
Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

$ composer require erfans/maintenance-bundle "~2.0"
This command requires you to have Composer installed globally, as explained
in the installation chapter
of the Composer documentation.

Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the app/AppKernel.php file of your project:

<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Erfans\MaintenanceBundle\ErfansMaintenanceBundle(),
        );

        // ...
    }

    // ...
}
Step 3: Add Routing

Add maintenance default controller route to app/config/routing.yml

erfans_maintenance:
    resource: "@ErfansMaintenanceBundle/Resources/config/routing.yml"
if you want to use your own controller and action for maintenance page
or use some external link or just an html page then you can skip this part.

Step 4: Configuration

To check if current request is under maintenance this bundle will check
defined "include/exclude" rules under configuration part. This approach
will provide maximum flexibility to put part of your website under
maintenance mode or just exclude some pages from maintenance.

To add a "include" rule set parameter "rule" to "+" and to add a exclude
rule set "rule" to "-". Available variables for each rule to be check are:

rule: # One of "+"; "-", Required
env:  # Requested environment e.g. "test" or ["dev", "prod"]
path: # Regular expression for request path e.g. ^/* to cover all requests
routes: # To compair with current route e.g. ["home_route"]
host: # Requested host
schemes: # e.g. http or https
methods: # Requested method e.g. ["get", "post"]
usernames: # Username of current user
roles: # Role of current user
ips: # IP of visitor
If no rule will be defined default rules will be applied which are:

rules:
    - {rule: '+', path: '^/*'} # include all paths into maintenance mode
    - {rule: '-', path: '^/login$'} # exclude usual path for login from maintenance
    - {rule: '-', roles: ['ROLE_ADMIN']} # exclude Admin role from maintenance
    - {rule: '-', env: ['test', 'dev']} # exclude environments "test" and "dev" from maintenance
Defining rules may seems too much effort for simple task, however, it is
handy when you want to develop new parts website without interfering other
parts.

Default configuration for "ErfansMaintenanceBundle":

erfans_maintenance:
    enabled:              false

    # After due-date maintenance mode will not invoke anymore. Date format should be 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD HH:MM:SS +/-TT:TT' or timestamp
    due_date:             null # Example: 2016-7-6 or 2016-7-6 10:10 or 2016-7-6 10:10:10 +02:00 or 1467763200

    # It is possible to change corresponding controller by changing the route name.
    maintenance_route:    erfans_maintenance_maintenance

    # Maintenance page can be an external link or only an html page.
    maintenance_url:      ~

    # View parameters will set on default twig template of maintenance bundle. These values will translate before rendering
    view:
        title:                erfans.maintenance.messages.under_construction.title
        description:          erfans.maintenance.messages.under_construction.description

    # To provide maximum flexibility to put part of website on maintenance mode by defining 'include' or 'exclude' rules.
    rules:                # Example: - {rule: '+', path: '^/*'} # to set maintenance mode for whole website
        - {rule: '+', path: '^/*'}           # include all paths into maintenance mode
        - {rule: '-', path: '^/login$'}      # exclude usual path for login from maintenance
        - {rule: '-', roles: ['ROLE_ADMIN']} # exclude Admin role from maintenance
        - {rule: '-', env: ['test', 'dev']}  # exclude environments "test" and "dev" from maintenance

    # By enabling "redirect_on_normal" website will redirect from maintenance page if maintenance mode is disabled.
    redirect_on_normal:
        enabled:              true

        # Application will redirect from maintenance page to this url if maintenance_mode is false. You can only set one of redirect_url or redirect_route
        redirect_url:         /
        redirect_route:       ~