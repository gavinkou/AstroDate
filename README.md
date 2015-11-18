AstroDate
=========
AstroDate is a PHP date/time library that also provides useful astronomy related functionality. 


Installation
------------
The package can be installed with `composer` using the following vendor/package name:
```
$ composer require marando/astrodate
```

Package Classes
---------------

 Class       | Description
-------------|---------------------------------------------
 `AstroDate` | Represents a Date/Time
 `Epoch`     | Represents an Epoch (e.g. J2000.0)
 `TimeScale` | Represents a time scale (such as TT or TAI)
 `TimeZone`  | Represents a UTC time zone 
 `YearType`  | Represents a YearType (Julian/Besselian)


AstroDate Usage
---------------


### Creating Instances
#### Default Constructor
To create a new instance from a date and optional time you can use the `create()` static constructor which is an alias of the default constructor:
```php
AstroDate::create(2015, 11, 15);
AstroDate::create(2015, 11, 15, 2, 35, 10);
```
```
2015-Nov-15 00:00:00.000 UTC
2015-Nov-15 02:35:10.000 UTC
```
If you wish you can also supply a time zone or time scale (such as TT) as follows:
```php
AstroDate::create(2015, 11, 15, 2, 35, 10, TimeZone::parse('EST'));
AstroDate::create(2015, 11, 15, 2, 35, 10, null, $ts = TimeScale::TT());
```
```
2015-Nov-14 02:35:10.000 EST
2015-Nov-15 02:35:10.000 TT
```
#### Parsing Date/Time Strings
You can parse a date/time string using the `parse()` static constructor:
```php
AstroDate::parse('2015-Nov-15 5:45:10 EST');
AstroDate::parse('2015-Nov-15 5:45:10 TT');
```
```
2015-Nov-15 05:45:10.000 EST
2015-Nov-15 05:45:10.000 TT
```
Currently only a few formats are supported, but more will be added in the future.

#### Current Time
The `now()` method will return the current time and accepts an optional timezone:
```php
AstroDate::now();
AstroDate::now('EST');
```
```
2015-Nov-18 04:35:42.308 UTC
2015-Nov-17 23:35:42.308 EST
```
#### Creation from JD and MJD
Creation from Julian and Modified Julian dates can be done as follows:
```php
AstroDate::jd(2451545.5);
AstroDate::mjd(57482.03847);
```
```
2000-Jan-02 00:00:00.000 UTC
2016-Apr-04 00:55:23.000 UTC
```
And like above you can specify an optional time standard as follows:
```php
AstroDate::jd(2451545.5, TimeScale::TT());
AstroDate::mjd(57482.03847, TimeScale::TT());
```
```
2000-Jan-02 00:00:00.000 TT
2016-Apr-04 00:55:23.000 TT
```

#### Equinoxes and Solstices
There is also the ability to create an instance from the date and time of a Equinox or Solstice for a provided year:
```php
AstroDate::equinoxSpring(2020);
AstroDate::solsticeSummer(2020);
AstroDate::equinoxAutumn(2020);
AstroDate::solsticeWinter(2020);
```
```
Results:
2020-Mar-20 03:51:09.000 TT
2020-Jun-20 21:44:36.000 TT
2020-Sep-22 13:32:01.000 TT
2020-Dec-21 10:04:00.000 TT
```
By default the dates are returned in the Terrestrial Time scale but they can be converted to another time scale using one of the appropriate methods that will be discussd further below. Also, the current method for Equinox or Solstice calculation employed is of lower accuracy, and a higher algorithm is expected to be implemented in the future.


### Date & Time Property Components
Each instance has the following properties:

```php
$d = AstroDate::create(2015, 11, 10, 9, 17, 43.750);

// Date
$d->year;       // 2015
$d->month;      // 11
$d->day;        // 10

// Time
$d->hour;       // 9
$d->min;        // 17
$d->sec;        // 43
$d->micro;      // 750000000

// Other 
$d->era;        // A.D.
$d->timezone;   // UTC
$d->timescale;  // UTC
```
Each property is also writable:
```php
print AstroDate::create(2015, 11, 10, 9, 17, 43.750);
$d->hour = 23;
print $d;
```
```
Results:
2015-Nov-10 09:17:43.750 UTC
2015-Nov-10 23:17:43.750 UTC
```
You can also use the `setDate()`, `setTime()` and `setDateTime()` methods for writing to properties


### Converting to Another Time Zone
```php
$d = AstroDate::now();
$d->setTimezone('EST');
$d->setTimezone('UT+04:30');
```
```
2015-Nov-18 05:38:44.877 UTC
2015-Nov-18 00:38:44.877 EST
2015-Nov-18 10:08:44.877 UT+04:30
```

### Converting to Astronomical Time Scales
```php
$d = AstroDate::now();
$d->toTAI();
$d->toTT();
$d->toTDB();
```
```
2015-Nov-18 05:41:11.621 UTC
2015-Nov-18 05:41:47.621 TAI
2015-Nov-18 05:42:19.805 TT
2015-Nov-18 05:42:19.804 TDB
```

### To Julian Date
```php
$d = AstroDate::create(2015, 12, 10, 11, 15, 35.125);
$d->toJD();   // Julian Date
$d->toMJD();  // Modified Julian Date
```
```php
2457366.9691565
57366.46915654
```
Since PHP's float object has a finite precision you can pass a precision and the JD will be computed further and returned as a string:
```php
$d = AstroDate::create(2015, 12, 10, 11, 15, 35.125);
$d->toJD(15);
$d->toMJD(15);
```
```
2457366.969156539351850
  57366.469156539351850
```



### Sidereal Time
#### Sidereal Time at Greenwich
```php
AstroDate::now()->sidereal('a');  // Apparent sidereal time
AstroDate::now()->sidereal('m');  // Mean sidereal time
```
```
9.323 hours
9.324 hours
```
#### Local Sidereal Time
```php
                                // Specify local longitude
AstroDate::now()->sidereal('a', Angle::deg(-90));
AstroDate::now()->sidereal('m', Angle::deg(-90));
```
```
3.361 hours
3.361 hours
```

### Mathematical Operations
#### Adding/Subtracting a Time Duration
```php
print AstroDate::create(2015, 11, 20, 9, 17, 43.750);
print $d->add(Time::days(10));
print $d->sub(Time::days(10));
```
```
Results:
2015-Nov-30 09:17:43.750 UTC
2015-Nov-10 09:17:43.750 UTC
```
#### Difference Between Two Dates
```php
print $d1 = AstroDate::create(2015, 11, 10);
print $d2 = AstroDate::create(2020, 11, 10);
print $d1->diff($d2);
```
```
Results:
2015-Nov-10 00:00:00.000 UTC
2020-Nov-10 00:00:00.000 UTC
1827 days
```
### Day and Month Names
#### Day Name
```php
AstroDate::create(2015, 11, 10)->dayName();
AstroDate::create(2015, 11, 10)->dayName(false);
```
```
Results:
Tuesday
Tue
```
#### Month Name
```php
AstroDate::create(2015, 11, 10)->monthName();
AstroDate::create(2015, 11, 10)->monthName(true);
```
```
Results:
Nov
November
```

### Time Since and Until Midnight
```php
AstroDate::create(2015, 11, 10, 14, 25, 12)->sinceMidnight();
AstroDate::create(2015, 11, 10, 14, 25, 12)->untilMidnight();
```
```
Results:
14.42 hours
9.58 hours
```

### Leap Year
You can get if a year is a leap year or not from the `isLeapYear()` function:
```php
$d1 = AstroDate::create(2015, 11, 10)->isLeapYear();
$d2 = AstroDate::create(2016, 11, 10)->isLeapYear();

var_dump($d1);
var_dump($d2);
```
```
bool(false)
bool(true)
```
### Day of the Year
The day number out of the year can be returned as follows:
```php
AstroDate::create(2015, 11, 10, 9)->dayOfYear()
```
```
Results:
314
```

### Formatting Date/Time Strings
Instances can be converted to whatever format you wish using the standard PHP format Date & Time characters. For example:

```php
AstroDate::now()->format('Y-M-d H:m:s');
```
```
2015-Nov-18 05:11:24
```
The `AstroDate` class provides a few default formats:

 Constant Name    | Format            | Example
------------------|-------------------|------------------------------
 `FORMAT_DEFAULT` | `Y-M-d H:i:s.u T` | 2015-Nov-18 05:15:01.400 UTC
 `FORMAT_JPL`     | `r Y-M-c T`       | A.D. 2015-Nov-18.2187713 UTC
 `FORMAT_EPOCH`   | `Y M. c T`        | 2015 Nov. 18.2187717 UTC

…but you can use whatevr format you want with the following format codes:

##### Day

     | Description 
-----|----------------------------------------------------------------------
 `d` | Day of the month, 2 digits with leading zeros
 `D` | Textual representation of a day, three letters
 `j` | Day of the month without leading zeros
 `l` | A full textual uppercase representation of the day of the week
 `L` | A full textual lowercase representation of the day of the week
 `N` | ISO-8601 numeric representation of the day of the week 1=Mon, 7=Sun
 `S` | English ordinal suffix for the day of the month, 2 characters
 `w` | Numeric representation of the day of the week 0=Sun, 6=Sat
 `z` | The day of the year (starting from 1)

##### Week

     | Description 
-----|----------------------------------------------------------------------
 `W` | ISO-8601 week number of year, weeks starting on Monday

##### Month

     | Description 
-----|----------------------------------------------------------------------
 `F` | A full textual representation of a month, such as January or March
 `m` | Numeric representation of a month, with leading zeros
 `M` | A short textual representation of a month, three letters
 `n` | Numeric representation of a month, without leading zeros
 `t` | Number of days in the given month
 
##### Year

     | Description 
-----|----------------------------------------------------------------------
 `Y` | A full numeric representation of a year, 4 digits
 `y` | A two digit representation of a year
 
##### Time

     | Description 
-----|----------------------------------------------------------------------
 `a` | Lowercase Ante meridiem and Post meridiem
 `A` | Uppercase Ante meridiem and Post meridiem
 `g` | 12-hour format of an hour without leading zeros
 `G` | 24-hour format of an hour without leading zeros
 `h` | 12-hour format of an hour with leading zeros
 `H` | 24-hour format of an hour with leading zeros
 `i` | Minutes with leading zeros
 `s` | Seconds with leading zeros
 `u` | Milliseconds
 
##### Time Zone

     | Description 
-----|----------------------------------------------------------------------
 `e` | Timezone/Timescale identifier
 `O` | Difference to UTC in hours
 `P` | Difference to UTC in hours with colon
 `Z` | Timezone offset in seconds
 
##### Misc

     | Description 
-----|----------------------------------------------------------------------
 `r` | Era, A.D. or B.C. (added for AstroDate)
 `c` | Day with fraction (added for AstroDate)





Epoch Usage
-----------

### Creation

#### Predefined Epochs
```php
Epoch::J2000();
Epoch::B1950();
Epoch::J1900();
Epoch::B1900();
Epoch::JMod();
```

#### Julian Epoch of Given Year
```php
Epoch::J(2015);
```

#### Besselian Epoch of Given Year
```php
Epoch::B(1940);
```

#### From JD
You can create an `Epoch` from a Julian date as shown:
```php
Epoch::jd(2451545);       // Result: J2000.0
Epoch::jd(2457548.2934);  // Result: 2016 Jun. 8.7933912 TT
```

#### From an `AstroDate` Instance
```php
Epoch::dt(AstroDate::now());
```
```
2015 Nov. 18.2479013 TT
```
































