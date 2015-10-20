# AstroDate

#### Julian Day
```php
echo AstroDate::parse('2015-Jan-1')->jd  // Julian day  Output: 2457023.5
```

#### Conversion to Astronomical Time Standards

##### International Atomic (TAI)
```php
echo AstroDate::parse('2015-Jan-1')->toTAI()
```
```
Output:

2015-Jan-01 00:00:35 TAI
```

##### Terrestrial Dynamic Time (TT or TDT)
```php
echo AstroDate::parse('2015-Jan-1')->toTT()
```
```
Output:

2015-Jan-01 00:01:07.184 TT
```


##### Barycentric Dynamic Time (TDB)
```php
echo AstroDate::parse('2015-Jan-1')->toTDB()
```
```
Output:

2015-Jan-01 00:01:07.186 TDB
```





