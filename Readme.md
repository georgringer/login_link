# TYPO3 Extension `login_link`

This extensions makes it possible to login into any BE or FE user by a link generated in the backend.

## Installation

Install this extension via composer by using the following command:

```bash
composer require georgringer/login-link
```

## Usage

### Login links for BE users

The login link can only be generated for non admin users

!! Currently nothing is logged when a user logs in via a login link !!

### Login links for FE users

Set the following Page TsConfig to enable the login link for FE users:

```typoscript
# 123 is the page which is used to login users, e.g. the login page
tx_loginlink.fe.loginPage = 123
```
