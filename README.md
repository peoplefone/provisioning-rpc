# Provisioning RPC

This package allows you to check / add / remove MAC addresses for the XML-RPC Server of:
* Auerswald
* Gigaset
* Panasonic
* Snom
* Yealink

A valid manufacturer's RPC server login is required.

## Installation

```bash
composer require peoplefone/provisioning-rpc
```

## Example

Scrolling down, you will find an example for each manufacturer.

```php
require("vendor/autoload.php");
use \Peoplefone\ProvisioningRPC;
use \Peoplefone\ProvisioningRPCResult;

$rpc = ProvisioningRPC::connect('manufacturer', ['username','password']);

$result = $rpc->checkPhone("123456ABCDEF");
$result = $rpc->addPhone("123456ABCDEF", "https://provisioningserver.domain.com", true);
$result = $rpc->removePhone("123456ABCDEF");
```

## Functions and Results

Three functions are available.
* checkPhone
* addPhone
* removePhone

Each function returns an object of type ProvisioningRPCResult.

```php
Peoplefone\ProvisioningRPCResult Object
(
    [mac] => string
    [result] => bool
    [code] => int
    [message] => string
)
```

### checkPhone

```php
checkPhone(string $mac) : ProvisioningRPCResult
```

The MAC address is lowercase formatted and all punctuations are removed.

| Code | Constant                                       | Description                                |
| ---- | ---------------------------------------------- | ------------------------------------------ |
| 1    | ProvisioningRPCResult::resultSucceeded         | The MAC Address is configured              |
| 0    | ProvisioningRPCResult::macNotFound             | The MAC Address is not configured          |
| -1   | ProvisioningRPCResult::macInvalid              | The MAC Address is invalid                 |
| -2   | ProvisioningRPCResult::macOwnedBySomeoneElse   | The MAC Address is owned by someone else   |
| -10  | ProvisioningRPCResult::unknownError            | Unknown error                              |
| -20  | ProvisioningRPCResult::connectionError         | Connection Error                           |

### addPhone

```php
addPhone(string $mac, string $url, bool $overwrite) : ProvisioningRPCResult
```

The MAC address is lowercase formatted and all punctuations are removed.

Before adding the phone, the checkPhone function is called.

| Code | Constant                                       | Description                                |
| ---- | ---------------------------------------------- | ------------------------------------------ |
| 1    | ProvisioningRPCResult::resultSucceeded         | The MAC Address has been added             |
| -1   | ProvisioningRPCResult::macInvalid              | The MAC Address is invalid                 |
| -2   | ProvisioningRPCResult::macOwnedBySomeoneElse   | The MAC Address is owned by someone else   |
| -3   | ProvisioningRPCResult::macAlreadyExists        | The MAC Address has already been added     |
| -4   | ProvisioningRPCResult::urlInvalid              | The Provisioning URL is invalid            |
| -5   | ProvisioningRPCResult::profileNameInvalid      | The Profile Name  is invalid               |
| -10  | ProvisioningRPCResult::unknownError            | Unknown error                              |
| -20  | ProvisioningRPCResult::connectionError         | Connection Error                           |

Please note: MAC addresses that do not belong to your account cannot be overwritten!

### removePhone

```php
removePhone(string $mac) : ProvisioningRPCResult
```

The MAC address is lowercase formatted and all punctuations are removed.

Before removing the phone, the checkPhone function is called.

| Code | Constant                                       | Description                                |
| ---- | ---------------------------------------------- | ------------------------------------------ |
| 1    | ProvisioningRPCResult::resultSucceeded         | The MAC Address has been removed           |
| 0    | ProvisioningRPCResult::macNotFound             | The MAC Address is not configured          |
| -1   | ProvisioningRPCResult::macInvalid              | The MAC Address is invalid                 |
| -2   | ProvisioningRPCResult::macOwnedBySomeoneElse   | The MAC Address is owned by someone else   |
| -10  | ProvisioningRPCResult::unknownError            | Unknown error                              |
| -20  | ProvisioningRPCResult::connectionError         | Connection Error                           |

Please note: MAC addresses that do not belong to your account cannot be deleted!

## Example by Manufacturer

### Auerswald

```php
$login_data = ['username','password'];

$rpc = ProvisioningRPC::connect('auerswald', $login_data);

$result = $rpc->checkPhone("123456ABCDEF");
$result = $rpc->addPhone("123456ABCDEF", "https://provisioningserver.domain.com/<MACADR>/", true);
$result = $rpc->removePhone("123456ABCDEF");
```

### Gigaset

```php
$login_data = ['username','password'];

$rpc = ProvisioningRPC::connect('gigaset', $login_data);
$rpc->setProfileName('profile_name'); // default = peoplefone

$result = $rpc->checkPhone("123456ABCDEF-1234");
$result = $rpc->addPhone("123456ABCDEF-1234", "https://provisioningserver.domain.com/%MACD/%DVID/", true);
$result = $rpc->removePhone("123456ABCDEF-1234");
```

### Panasonic

```php
$login_data = ['username','password'];

$rpc = ProvisioningRPC::connect('panasonic', $login_data);

$result = $rpc->checkPhone("123456ABCDEF");
$result = $rpc->addPhone("123456ABCDEF", "https://provisioningserver.domain.com/{MAC}/", true);
$result = $rpc->removePhone("123456ABCDEF");
```

### Snom

```php
$login_data = ['username','password'];

$rpc = ProvisioningRPC::connect('snom', $login_data);

$result = $rpc->checkPhone("123456ABCDEF");
$result = $rpc->addPhone("123456ABCDEF", "https://provisioningserver.domain.com/{mac}/", true);
$result = $rpc->removePhone("123456ABCDEF");
```

### Yealink

```php
$login_data = ['username','password'];

$rpc = ProvisioningRPC::connect('yealink', $login_data);

$result = $rpc->checkPhone("123456ABCDEF");
$result = $rpc->addPhone("123456ABCDEF", "configured_server_name", true);
$result = $rpc->removePhone("123456ABCDEF");
```
