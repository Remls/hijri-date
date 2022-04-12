# HijriDate

Laravel helper package for Hijri dates. Supports displaying dates in Dhivehi, Arabic and English.

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
HijriDate::getEstimateFromGregorian();          // Today's date
$input = Carbon::parse('2002-03-04');
HijriDate::getEstimateFromGregorian($input);    // 20th Dhul-Hijja 1422
```

## Available methods

```php
use Remls\HijriDate\HijriDate;

$date = new HijriDate(1443, 9, 1);          // 1st Ramadan 1443
$date->addDays(1);                          // 2nd Ramadan 1443

// Default locale is DV. This can be changed in config.
$date->toFullDate();                        // '2 ރަމަޟާން 1443'
$date->setLocale('AR')->toFullDate();       // '2 رمضان 1443'

$date->toDateString();                      // '1443-09-02'
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

This will automatically store data as `YYYY-MM-DD` string in database, and cast to `Remls\HijriDate\HijriDate` when accessing.

## Validation

Any string that passes the following conditions is considered a valid Hijri date:
- in the format `YYYY-MM-DD`
- year between 1000 and 1999
- month between 1 and 12
- day between 1 and 30

```php
use Remls\HijriDate\Rules\ValidHijriDate;

...
request()->validate([
    'your_hijri_date_field' => ['required', new ValidHijriDate],
]);
```

## Further improvements

- getEstimateFromGregorian(): use Carbon::parse if string is input
- subDays()
- Comparison methods
- Support more date formats
- Translations:
  - Customizable month strings
  - Customizable validation error message
  - Support more languages
