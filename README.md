Resource Bundle
========================
Installation
------------

Add the bundle to your `composer.json`:

    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:igdr/ResourceBundle.git"
        }
    ],

    "igdr/resource-bundle" : "dev-master"

and run:

    php composer.phar update

Then add the ResourceBundle to your application kernel:

    // app/IgdrKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Igdr\Bundle\ResourceBundle\IgdrResourceBundle(),
            // ...
        );
    }
