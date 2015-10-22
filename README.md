AstroDate
=========

#### Creating Instances
```php
AstroDate::now();                 // Current date and time  Output: 2015-Oct-21 02:59:09.318 UTC
AstroDate::parse('2017-Oct-15');  // Parse date             Output: 2015-Oct-21 02:59:09.318 UTC
```

#### From Julian Day
```php
AstroDate::jd(2457023.5)                        // Result: 2015-Jan-01 00:00:00
AstroDate::jd(2457023.5007776, TimeStd::TDB())  // Result: 2015-Jan-01 00:00:00
```

#### To Julian Day
```php
AstroDate::parse('2015-Jan-1')->jd  // Result: 2457023.5
```

#### Conversion to Astronomical Time Standards

```php
AstroDate::parse('2015-Jan-1')->toTAI()  // Result: 2015-Jan-01 00:00:35 TAI
AstroDate::parse('2015-Jan-1')->toTT()   // Result: 2015-Jan-01 00:01:07.184 TT
AstroDate::parse('2015-Jan-1')->toTDB()  // Result: 2015-Jan-01 00:01:07.186 TDB
```

#### Sidereal Time
```php
$d = AstroDate::parse('2015-Oct-19 14:36:04');

// Greenwich Mean Sidereal Time
echo $d->gmst();                      // At Greenwich
echo $d->gmst(Angle::deg(-82.4764));  // At local longitude

// Greenwich Apparent Sidereal Time
echo $d->gast();                      // At Greenwich
echo $d->gast(Angle::deg(-82.4764));  // At local longitude 
```
```
Output:
16ʰ27ᵐ4ˢ.661
10ʰ57ᵐ10ˢ.325
16ʰ27ᵐ4ˢ.578
10ʰ57ᵐ10ˢ.242
```


#### Addition and Subtraction
```php
$d = AstroDate::parse('2015-Oct-1');  // Result: 2015-Oct-01 12:00:00.000 

$d->add(Time::min(32));               // 2015-Oct-01 12:32:00.000 
$d->subtract(Time::min(12));          // 2015-Oct-01 12:20:00.000 
```

#### Difference between Two Dates

```php
$a = new AstroDate::parse('2015-10-1 11:54:01');
$b = new AstroDate::parse('2017-6-14 10:45:12');

$a->diff($b);  // Result: -621.952 days
$b->diff($a);  // Result: 621.952 days
```

#### To JPL Format
```php
AstroDate::now()->formatJPL();      // Day Fraction  Result: A.D. 2015-Oct-21.1272776 UTC
AstroDate::now()->formatJPL(true);  // hh:mm:ss      Result: A.D. 2015-Oct-21 03:03:16.840 UTC.
```

#### Leap Seconds
```php
AstroDate::parse('2015-Jan-1')->leapSec  // Result: 35
AstroDate::parse('2015-Oct-1')->leapSec  // Result: 36
```



