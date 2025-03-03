# Google Cloud Storage File System

The Google Cloud Storage File System (gcsfs) module provides an additional file
system to a Drupal site, allowing files to be stored in a Google Cloud Storage
bucket.

## Table of contents

- Requirements
- Recommended modules
- Installation
- Configuration
- Aggregated CSS/JS
- Image Styles
- Troubleshooting
- FAQ
- Maintainers

## Requirements

Drupal:

* No modules outside of Drupal core.

Google:

* The [Google Cloud PHP SDK](https://github.com/googleapis/google-cloud-php) is required.
* A Google Cloud Platform account.
* A Google Cloud Platform service account with read, write and list access to the bucket that this module will use.

## Recommended modules

None.

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

1. Enable the module at Administration > Extend.
2. Set the Bucket Name and the Credential file at Administration > Configuration > Media > File System > Google Cloud Storage.
3. Test the credentials to ensure that the credentials file works and has the proper access to the bucket.
4. Set the site to use the Google Cloud Storage File System at Administration > Configuration > Media > File System or set a file field to use the Google Cloud Storage File System when a file field is created.

## Aggregated CSS/JS

There is no way to take over the Public filesystem with this module so aggregated CSS and JS will remain serverd from the local and not Google Cloud Storage.

## Image Styles

Image styles work out of the box with the Google Cloud Storage File System since the structure of the image style tells which File System is creating the style. For example:

/sites/default/files/styles/thumbnail/gs/file.png => /sites/default/files/styles/{style}/{scheme}/file.png

The {scheme} tells Drupal which File System to use.

## Troubleshooting

None.

## FAQ

None.

## Maintainers

Current maintainers:

  * [slydevil](https://www.drupal.org/u/slydevil)
