# Changelog

All notable changes will be documented in this file.

## v1.5.1
- Incorrect OTP retry time calculation by @imahmood in #24

## v1.5.0
- Laravel 11.x Support by @imahmood in #22
- add laravel 11 support to the tests by @salehhashemi1992 in #23

## v1.4.3
- add prefix for otp rate limiter middleware by @salehhashemi1992 in #19
- otp invalidation after unsuccessful otp verification attempts by @salehhashemi1992 in #20
- auto-delete the OTP code after successful verification by @salehhashemi1992 #21

## v1.4.0
- implement otp rate limiter middleware by @salehhashemi1992 in #18

## v1.3.1
- nullable type for default null value by @salehhashemi1992 in #17

## v1.3.0
- Add pint and phpstan to ci steps by @salehhashemi1992 in #15
- moved resources/lang directory to the root directory by @imahmood in #16

## v1.2.0
- add codecov to ci steps by @salehhashemi1992 in #7
- Generate coverage report by @salehhashemi1992 in #8
- Add tests for OtpDto and SentOtpDto by @salehhashemi1992 in #9
- update checkout version to 4 by @salehhashemi1992 in #10
- Add ai pr describe to ci by @salehhashemi1992 in #11
- fix phpstan config by @salehhashemi1992 in #12
- Update phpstan level from 4 to 8 by @salehhashemi1992 in #13

## v1.1.3
- add codecov to ci steps by @salehhashemi1992 in #7
- Generate coverage report by @salehhashemi1992 in #8
- Add tests for OtpDto and SentOtpDto by @salehhashemi1992 in #9
- update checkout version to 4 by @salehhashemi1992 in #10
- Add ai pr describe to ci by @salehhashemi1992 in #11
- fix phpstan config by @salehhashemi1992 in #12

## v1.1.1
- default 4 digits otp range

## v1.1.0
- OtpManager facade implementation

## v1.0.0
- make enum type nullable

## v0.9.8
- enum type interface

## v0.9.5
- mobile custom validator
- bug fix

## v0.9.0
- Initial version
