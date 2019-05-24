# H5P Platform Integration Plugin for Neos CMS

[Watch this video for an introduction to H5P in Neos!](https://www.youtube.com/watch?v=eslFaQ3oj7E)

# What is H5P?
H5P is an open standard for rich content in browsers, mainly - but certainly not limited to - for learning use cases and
environments. It provides a variety of reusable content types, such as Memory Games, Fill in the Blanks, Multiple Choice
Questions and many more. Too get an overview about H5P and its content types, visit https://h5p.org/content-types-and-applications.

# What does this plugin do?
This plugin provides a platform integration for H5P into the [Neos Content Application Platform](https://neos.io), allowing
you to use H5P content types seamlessly within Neos.

# Maintenance and Compatibility
Sandstorm.NeosH5P is currently being maintained for the following versions:

| Neos Version  | Sandstorm.NeosH5P | Branch | Maintained |
|---------------|-------------------|--------|------------|
| 3.3           | 1.1.x             | 1.x    | Inactive   |
| 4.x           | 2.x               | master | Active     |

# Installation
Installation is simple and consists of these steps:
1. Run `composer require sandstorm/neosh5p`
2. Install the database model via `./flow doctrine:migrate`
3. Create a default set of configuration via `./flow h5p:generateconfig`
4. Make sure the H5P core assets (JS and CSS files) are published by running `./flow resource:publish`

That's it - you're good to use H5P content on your site. You new have a new plugin node type "H5P Content" that you
can use to integrate with H5P. You also have a set of backend modules to create and manage H5P content, libraries,
results and settings.

**Make sure to read and follow the integration guide below to really make the best use of the H5P package.**

# Usage
Refer to this video explaining how to use the H5P plugin: https://www.youtube.com/watch?v=eslFaQ3oj7E

# Integration Guide
## 1. Adjust Settings to your needs
Check the `Configuration/Settings.yaml` file and adjust the config settings to your needs. You should not need to change
anything under the `h5pPublicFolder` key, as this controls the publishing mechanism of the H5P plugin - this should work
out of the box. You can adjust anything under `configSettings` and `xAPI` the way you want it. The individual config
settings are described in the `Configuration/Settings.yaml` directly.

**Important:** After you changed anything under the `configSettings` key, you need to re-run the `./flow h5p:generateconfig`
command to bring it into the database and make it accessible to H5P. This is by design, as H5P is designed for these config
settings to be changed by an admin (which we haven't implemented yet, and likely never will). 

## 2. Configure saving of Content Results and Content State
H5P supports persisting content state (e.g. the currently selected answers to multiple choice questions) and content
results (e.g. answers given by a user to a multiple choice question). In order for this to work in Neos, we need to
add a few settings. 

In your site package's `Policy.yaml`, provide the permission to use the controller actions to the role which you
want to be able to use them. If you're using a frontend login package like [Sandstorm.UserManagement](https://github.com/sandstorm/UserManagement),
your config could look like below. Replace the role `Sandstorm.UserManagement:User` with the one your frontend users
actually have. If you want to save content results and user data even for non-logged-in users, set this to
`Neos.Flow:Everybody`. Mind the GDPR implications of this, as you're then persisting data of every user that
interacts with H5P content on your site. 

```YAML
roles:
  Sandstorm.UserManagement:User:
    privileges:  
      -
        privilegeTarget: 'Sandstorm.NeosH5P:FrontendControllerActions'
        permission: GRANT
```

## 3. Integrate xAPI
You can control which username and email address is sent in xAPI statements by implementing your own implementation of
the `FrontendUserServiceInterface`. Check the default `Sandstorm\NeosH5P\Domain\Service\FrontendUserService` for an
example. You need to configure Neos to use your implementation in your site package's `Objects.yaml`. This will give 
you the opportunity to interact with your own frontend user model and extract name and email address.

To send xAPI statements to a LRS or other external endpoint, you need to provide a JavaScript that handles the sending
process. This way, you have control over the sending process and can handle any login/routing/statement manipulation 
requirements in your own package. The script is injected automatically - all you need to do is provide a path to a
script file under the configuration setting `Sandstorm.NeosH5P.xAPI.integrationScript`. Refer to this package's
`Settings.yaml` for a detailed explanation (see the comments at the "xAPI" section of the config).

## 4. Set up a cronjob to remove temporary H5P editor files
Plan a cronjob to remove all EditorTempfiles and associated resources. EditorTempfiles are created e.g. when a user
starts creating an H5P content element and has already uploaded assets (such as pictures), then cancels the process.
We provide a CLI command for this: `./flow h5p:cleareditortempfiles`. Depending on how many users you have that are
creating H5P content, this should be run about once a week.

# Known issues / remaining TODOs
* Internationalization is not implemented yet.
* Form validation in the H5P editor is not taken into account.
* Flash messages do not work in the fullscreen content editor.
* Methods for usage statistics are not implemented yet.
* Config settings can not be changed in the GUI, only via CLI command (which is probably fine).

# License
License: MIT.

# Sponsor
Thanks to our valued customer [Deutsches Institut für Erwachsenenbildung Bonn](https://die-bonn.de) for sponsorship of
this package!
