# HijriDate

Laravel helper package for Hijri dates. Supports displaying dates in Dhivehi, Arabic and English out of the box, with support for further customizations in a language of your choice.

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
use Remls\HijriDate\HijriDate;
use Carbon\Carbon;

new HijriDate();                    // 1st Muharram 1000
new HijriDate(1443, 9, 1);          // 1st Ramadan 1443
HijriDate::parse('1443-09-01');     // 1st Ramadan 1443

// From Gregorian
HijriDate::getEstimateFromGregorian();              // Today's date
HijriDate::getEstimateFromGregorian('1991-12-01');
$input = Carbon::parse('2002-03-04');               // 25th Jumada al-Ula 1412
HijriDate::getEstimateFromGregorian($input);        // 20th Dhul-Hijja 1422
```

## Available methods

```php
use Remls\HijriDate\HijriDate;

$date = new HijriDate(1443, 9, 1);          // 1st Ramadan 1443
$date->addDays(1);                          // 2nd Ramadan 1443
$date->subDays(3);                          // 29th Sha'ban 1443

// Default locale is DV. This can be changed in config.
$date->toFullDate();                        // '29 ޝަޢުބާން 1443'
$date->setLocale('AR')->toFullDate();       // '29 شعبان 1443'

$date->toDateString();                      // '1443-08-29'
```

## Casting

The field to be casted must be a string field on database.

```php
// App/Models/YourModel.php
use Remls\HijriDate\HijriDate;

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

## Further improvements

- Support more date formats
