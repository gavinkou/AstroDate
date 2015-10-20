# AstroDate

#### To Julian Day
```php
AstroDate::parse('2015-Jan-1')->jd  // Result: 2457023.5
```

#### From Julian Day
```php
AstroDate::jd(2457023.5)                        // Result: 2015-Jan-01 00:00:00
AstroDate::jd(2457023.5007776, TimeStd::TDB())  // Result: 2015-Jan-01 00:00:00
```

#### Conversion to Astronomical Time Standards

AstroDate::parse('2015-Jan-1')->toTAI()  // Result: 2015-Jan-01 00:00:35 TAI
AstroDate::parse('2015-Jan-1')->toTT()   // Result: 2015-Jan-01 00:01:07.184 TT
AstroDate::parse('2015-Jan-1')->toTDB()  // Result: 2015-Jan-01 00:01:07.186 TDB
```


#### Leap Seconds
```php
AstroDate::parse('2015-Jan-1')->leapSec  // Result: 35
AstroDate::parse('2015-Oct-1')->leapSec  // Result: 36
```



