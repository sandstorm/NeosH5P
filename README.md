# H5P Platform Integration Plugin for Neos CMS

# Integration Guide
## Policy.yaml
* define which users can access frontend routes (would usually be your frontend user)
## Settings.yaml
* check the default settings and overwrite them where necessary
* add a requestPattern for the H5P frontend controllers so we can get the account
## xAPI
* Control which username/mail is sent in xAPI statements by implementing your own xApiUserServiceInterface and
  configuring the plugin to use it via Objects.yaml. This will give you the opportunity to interact with your 
  own user model and extract name and email address.
## Cronjob to remove editor temp files
* Plan a cronjob to remove all EditorTempfiles and associated resources.

# License
License: MIT.

# Sponsor
Thanks die [Deutsches Institut für Erwachsenenbildung Bonn](https://die-bonn.de) for sponsorship!
