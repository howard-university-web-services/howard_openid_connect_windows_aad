# Howard OpenID Connect Windows Azure Active Directory

This small module is a CTools plugin for the great module OpenID Connect and
focuses on integration with Windows Azure AD. This is a custom module based on
[OpenID Connect Microsoft Azure Active Directory client](https://www.drupal.org/project/openid_connect_windows_aad)
module. It has been branched, and customized for Howard, as there are some
peculiarities that need to be accounted for.

This module, and sub-modules, contain markup only (no js or css), those should be provided in the client theme, loaded via the idfive Component Library:

- [idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)
- [idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)

## Features

- Provides howard specific customizations to auth against the HU Azure openid apps.
- Hides local password fields, as well as a few others on the user edit and registration forms.

## Installation and Updates

### Install Via Composer

`composer install howard/howard_openid_connect_windows_aad`

### Update Via Composer

`composer update howard/howard_openid_connect_windows_aad`

## Setup

- Install this module.
- Visit the OpenID Connect config page: admin/config/services/openid-connect.
- Howard Windows Azure AD will be available as a client.

## Requirements

- Drupal OpenID Connect module
- Windows Azure Active Directory endpoints from your registered application
