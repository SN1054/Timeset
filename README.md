# Timeset
 Timeset is a package that helps dealing with time intervals in PHP.
 Time intervals are considered as sets on [real line](https://en.wikipedia.org/wiki/Real_line). Aside from a few auxilary operations, only 4 basic operations are supported: OR, AND, XOR, NOT. [Others](https://en.wikipedia.org/wiki/Set_(mathematics)#Basic_operations) can berived from those.

## Features

- Flyweight
- No dependencies
- Easy to use

## Example

Imagine you have two employees, and they have their schedules and their appointments. You need to calculate when they both are free.
```
$firstEmployee = new stdClass();
$secondEmployee = new stdClass();

$firstEmployee->schedule = Set::create([
    ['2020-10-10 10:00:00', '2020-10-10 20:00:00'],
    ['2020-10-11 10:00:00', '2020-10-11 20:00:00'],
    // other intervals
]);

$firstEmployee->appointments = Set::create([
    ['2020-10-10 11:00:00', '2020-10-10 15:00:00'],
    ['2020-10-10 16:00:00', '2020-10-10 19:00:00'],
    // other intervals
]);

$secondEmployee->schedule = Set::create([
    ['2020-10-10 10:00:00', '2020-10-10 20:00:00'],
    ['2020-10-12 10:00:00', '2020-10-12 20:00:00'],
    // other intervals
]);

$secondEmployee->appointments = Set::create([
    ['2020-10-10 12:00:00', '2020-10-10 15:00:00'],
    ['2020-10-10 16:00:00', '2020-10-10 19:00:00'],
    // other intervals
]);

$intervalsOnWhichFirstEmployeeIsFree = $firstEmployee->schedule->and(
    $firstEmployee->appointments->not()
);

$intervalsOnWhichSecondEmployeeIsFree = $secondEmployee->schedule->and(
    $secondEmployee->appointments->not()
);

$intervalsOnWhichBothEmployeesAreFree = $intervalsOnWhichFirstEmployeeIsFree->and(
    $intervalsOnWhichSecondEmployeeIsFree
);

```
Now we can filter, for example, those intervals shorter than 3 hours:
```
$neededIntervals = array_filter(
    $intervalsOnWhichBothEmployeesAreFree->sets(), 
    fn($interval) => $interval->length()->h >= 3
);
```

