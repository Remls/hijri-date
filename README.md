<!-- omit in toc -->
# HijriDate

Laravel helper package for Hijri dates. Supports displaying dates in Arabic, Bengali, Dhivehi and English out of the box, with support for further customizations or adding a language of your choice.

- [Installation](#installation)
- [Creating dates](#creating-dates)
  - [Customizing how dates are converted between Hijri and Gregorian](#customizing-how-dates-are-converted-between-hijri-and-gregorian)
- [Available methods](#available-methods)
  - [Conversions](#conversions)
  - [Calculations](#calculations)
  - [Comparisons](#comparisons)
  - [Formatting](#formatting)
- [Casting](#casting)
- [Validation](#validation)
- [Localization](#localization)
  - [Adding a language](#adding-a-language)
- [Migrating from v1 to v2](#migrating-from-v1-to-v2)

## Installation

```
composer require remls/hijri-date
```

To publish configuration files:

```sh
php artisan vendor:publish --provider="Remls\HijriDate\HijriDateServiceProvider" --tag="config"
```

## Creating dates

All of the following methods return an instance of `Remls\HijriDate\HijriDate`.

```php
use Carbon\Carbon;

new HijriDate();                    // 1st Muharram 1000
new HijriDate(1443, 9, 1);          // 1st Ramadan 1443
HijriDate::parse('1443-09-01');     // 1st Ramadan 1443

// From Gregorian
HijriDate::createFromGregorian();              // Today's date
today_hijri();                                 // Today's date
HijriDate::createFromGregorian('1991-12-01');  // 20th Jumada al-Ula 1412
$input = Carbon::parse('2002-03-04');
HijriDate::createFromGregorian($input);        // 12th Dhul-Hijja 1422
```

### Customizing how dates are converted between Hijri and Gregorian

By default, the package uses [an external map](https://gist.github.com/Remls/b0ebba53bb2a8670f333f8a88de4aae3) between Hijri and Gregorian dates in Maldives to convert between the two. This map is cached and reused for subsequent conversions.

You may customize for how long the map is cached by changing `config/hijri.php` > `conversion.cache_period`.

You may manually re-fetch data from the external source by running `php artisan hijri:fetch`.

The package also comes with an alternative class for converting dates using calculations instead of a map. You may enable it by changing `config/hijri.php` > `conversion.converter` to `\Remls\HijriDate\Converters\MaldivesEstimateG2HConverter::class`.

You may customize how dates are converted by:

- providing your own map in `config/hijri.php` > `conversion.data_url`
- providing your own custom converter class in `config/hijri.php` > `conversion.converter`
  - The class must implement `\Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter`.

## Available methods

### Conversions

```php
// Gregorian to Hijri
HijriDate::createFromGregorian('1991-12-01'); // returns HijriDate

// Hijri to Gregorian
$date = new HijriDate(1443, 9, 1);
$date->getGregorianDate();                    // returns Carbon
```

You may customise how the conversion works, as detailed [here](#customizing-how-dates-are-converted-between-hijri-and-gregorian).

### Calculations

```php
$date = new HijriDate(1443, 9, 1);   // 1st Ramadan 1443
$date->addDays(1);                   // 2nd Ramadan 1443
$date->subDays(3);                   // 28th Sha'ban 1443
$date2 = new HijriDate(1443, 8, 20); // 20th Sha'ban 1443
$date->diffInDays($date2);           // 8
```

### Comparisons

You may compare two HijriDate objects `$a` and `$b` using the following methods:

| Method                         | Description                                                               |
| ------------------------------ | ------------------------------------------------------------------------- |
| `$a->compareWith($b)`          | Returns -1 if $a < $b.<br>Returns 0 if $a == $b.<br>Returns 1 if $a > $b. |
| `$a->equalTo($b)`              | Returns true if $a == $b.                                                 |
| `$a->greaterThan($b)`          | Returns true if $a > $b (a is after b).                                   |
| `$a->lessThan($b)`             | Returns true if $a < $b (a is before b).                                  |
| `$a->greaterThanOrEqualTo($b)` | Returns true if $a >= $b (a is after or equal to b).                      |
| `$a->lessThanOrEqualTo($b)`    | Returns true if $a <= $b (a is before or equal to b).                     |

### Formatting

Each HijriDate object will have a set locale when it is created. This locale will be used for formatting.

The locale is `'dv'` by default, but you may customize it by:

- passing locale in constructor (eg: `new HijriDate(1443, 9, 1, 'en')`)
- changing locale after creation (eg: `$date->setLocale('en')`)
- changing `default_locale` in configuration, so all HijriDate objects are created using that default locale

The following options are supported with `$date->format()`:

| Option             | Description                          | Example                 |
| ------------------ | ------------------------------------ | ----------------------- |
| d                  | Day of month (with leading zero)     | 01 ... 30               |
| D                  | Weekday (short)                      | Sun ... Sat             |
| j                  | Day of month (without leading zero)  | 1 ... 30                |
| l<br>(lowercase L) | Weekday                              | Sunday ... Saturday     |
| F                  | Month                                | Muharram ... Dhul-Hijja |
| m                  | Month (number, with leading zero)    | 01 ... 12               |
| M                  | Month (short)                        | Mhr ... DhH             |
| n                  | Month (number, without leading zero) | 1 ... 12                |
| Y                  | Year                                 | 1000 ... 1999           |
| y                  | Year (final two digits)              | 00 ... 99               |

```php
$date = new HijriDate(1443, 9, 1);  // 1st Ramadan 1443
$date->format("F");                 // "ރަމަޟާން" (using default locale 'dv')

$date->setLocale('ar');
$date->format("F Y");               // "رمضان 1443"
// Use numerals from locale
$date->format("F Y", true);         // "رمضان ١٤٤٣"
```

## Casting

The field to be cast must be a string field on database.

```php
// App/Models/YourModel.php

class YourModel
{
    ...

    protected $casts = [
        ...
        'your_hijri_date_field' => HijriDate::class,
    ];
}
```

This will automatically store data as `Y-m-d` string in database, and cast to `Remls\HijriDate\HijriDate` when accessing.

## Validation

Any string that passes the following conditions is considered a valid Hijri date:

- in the format `Y-m-d`
- year between 1000 and 1999 (This can be changed in config.)
- month between 1 and 12
- day between 1 and 30

```php
use Remls\HijriDate\Rules\ValidHijriDate;

...
request()->validate([
    'your_hijri_date_field' => ['required', new ValidHijriDate],
]);
```

Note that validation error messages will use app's locale (unlike formatting).

## Localization

Publish translation files by using:

```sh
php artisan vendor:publish --provider="Remls\HijriDate\HijriDateServiceProvider" --tag="lang"
```

You may then customize strings as needed.

### Adding a language

To add support for another language:

1. Publish the configuration file. The file will be copied to `config/hijri.php`.
2. Publish the translation files. The files will be copied to `lang/vendor/hijri`.
3. Copy one of the existing translation folders, and rename it with the language code of your choice. Eg: `lang/vendor/hijri/es`
4. Change strings to their respective translations.
5. Add the language code to `supported_locales` in `config/hijri.php`.
6. (Optional) Change `default_locale` in `config/hijri.php` to the new language code.

## Migrating from v1 to v2

The package no longer uses estimates when converting from Hijri to Gregorian by default.

- New keys have been added to `config/hijri.php`. You may need to update your configuration file.
- The function `getEstimateFromGregorian` has been **REMOVED** in favour of `createFromGregorian`.
  - To maintain the same behaviour as before:
    1. Change `config/hijri.php` > `conversion.converter` to `\Remls\HijriDate\Converters\MaldivesEstimateG2HConverter::class`.
    2. Change all calls from `getEstimateFromGregorian` to `createFromGregorian`.
- The function `isEstimate` has been **REMOVED**. There is no more need to check if the date was made from an estimate, as you can now always just get the corresponding Gregorian date with a call to `getGregorianDate`, regardless of how it was created.
- The function `getEstimatedFrom` has been **REMOVED** in favour of `getGregorianDate`.
- The function `resetEstimation` has been **REMOVED** in favour of `resetGregorianDate`.
- The functions `addDays` and `subDays` will now use the underlying Gregorian date by default for calculations.
  - To maintain the same behaviour as before, pass `false` to `$useGregorian` parameter of these functions.
