# Steps to upgrade H5P Core 

This document should help future maintainers of the NeosH5P plugin perform upgrades to newer 
versions of H5P core. Since the h5p core is not very upgrade-friendly, a lot of things need
to be done manually. Here's what I did when upgrading the core from 1.17 to 1.20.

## 1. Check the model classes for new/removed fields

Since the H5P core does not bring model classes, I did the following:
1. Install wordpress locally.
2. Get the correct version of the H5P WP plugin from 
   https://github.com/h5p/h5p-wordpress-plugin. Clone it into the wp-content/plugins folder,
   into a folder called `h5p`. Execute `git submodule update --init --recursive`.
3. Go through `class-h5p-plugin.php` and check the dbDelta functions for new/removed
   fields, comparing them against our model classes.
4. If necessary, create h5p elements and refer to the local db to see what is actually
   in the db fields.
5. If you added new fields, go through the model classes and make sure to reflect your
   changes in the model-to-associative-array functions that represent the adapter layer.
   For example in Content.php, these are `createFromMetadata`, `updateFromMetadata` and 
   `toAssocArray`.
   
   
## 2. Check for new/removed API functions
You should be seeing errors when compiling if new functions have been added to
core API classes like H5PFramework or H5PStorage. Refer to the core implementation
or the WP addon to see implementation examples.
