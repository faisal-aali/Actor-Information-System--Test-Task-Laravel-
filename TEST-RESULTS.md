
# Test Results

This document contains the results of running the test suite using `php artisan test`.

```bash
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   WARN  Tests\Unit\OpenAIServiceTest
  ✓ builds human like prompt correctly                                                                                                          0.12s  
  ✓ parses valid json response                                                                                                                  0.01s  
  ✓ parses json with markdown code blocks                                                                                                       0.01s  
  ✓ handles invalid json gracefully                                                                                                             0.02s  
  ✓ handles empty response                                                                                                                      0.01s  
  ✓ ensures all expected fields are present                                                                                                     0.01s  
  ✓ uses caching for similar requests                                                                                                           0.02s  
  - logs errors appropriately → Facade mocking not supported for final classes                                                                  0.02s  
  - health status returns healthy when service works → Facade mocking not supported for final classes                                           0.01s  
  - health status returns unhealthy when service fails → Facade mocking not supported for final classes                                         0.01s  

   WARN  Tests\Feature\ActorApiTest
  ✓ api prompt validation returns correct response                                                                                              0.06s  
  ✓ api prompt validation response format                                                                                                       0.01s  
  ✓ api prompt validation with different http methods                                                                                           0.02s  
  ✓ api prompt validation headers                                                                                                               0.01s  
  ✓ api prompt validation performance                                                                                                           0.01s  
  ✓ api prompt validation with concurrent requests                                                                                              0.03s  
  ✓ api prompt validation caching                                                                                                               0.01s  
  ✓ api prompt validation error handling                                                                                                        0.01s  
  ✓ api prompt validation content security                                                                                                      0.01s  
  ✓ api prompt validation with different accept headers                                                                                         0.01s  
  ✓ api prompt validation logging                                                                                                               0.01s  
  ✓ api prompt validation rate limiting                                                                                                         0.15s  
  ✓ api prompt validation with authentication                                                                                                   0.01s  
  ✓ api prompt validation cors headers                                                                                                          0.01s  
  ✓ api prompt validation with query parameters                                                                                                 0.01s  
  ✓ api prompt validation response size                                                                                                         0.01s  
  - api prompt validation with database interaction → Database interaction test skipped due to test environment setup                           0.01s  
  ✓ api prompt validation error responses                                                                                                       0.02s  
  ✓ api prompt validation consistency                                                                                                           0.02s  

   FAIL  Tests\Feature\ActorIntegrationTest
  ✓ complete actor submission workflow                                                                                                          0.27s  
  ✓ actor submission with missing required fields                                                                                               0.02s  
  ✓ actor submission with openai failure                                                                                                        0.02s  
  ✓ actor submission validation rules                                                                                                           0.03s  
  ✓ actor submission with duplicate email                                                                                                       0.02s  
  ✓ actor submissions page displays data correctly                                                                                              0.03s  
  ✓ actor submissions page with no data                                                                                                         0.02s  
  ✓ api prompt validation endpoint                                                                                                              0.02s  
  ✓ form displays correctly                                                                                                                     0.02s  
  ⨯ form validation error display                                                                                                               0.02s  
  ⨯ form preserves input on validation error                                                                                                    0.02s  
  ⨯ caching behavior                                                                                                                            0.02s  
  ✓ actor model relationships and casts                                                                                                         0.01s  
  ✓ database constraints                                                                                                                        0.02s  

   PASS  Tests\Feature\ActorPerformanceTest
  ✓ form page loads quickly                                                                                                                     0.03s  
  ✓ submissions page loads quickly with large dataset                                                                                           0.27s  
  ✓ actor submission performance                                                                                                                0.02s  
  ✓ database query performance                                                                                                                  0.03s  
  ✓ memory usage with large dataset                                                                                                             0.10s  
  ✓ concurrent actor submissions                                                                                                                0.05s  
  ✓ caching performance improvement                                                                                                             0.02s  
  ✓ database index performance                                                                                                                  0.17s  
  ✓ pagination performance                                                                                                                      0.32s  
  ✓ api endpoint performance                                                                                                                    0.01s  
  ✓ memory cleanup after large operations                                                                                                       0.15s  
  ✓ database connection pooling                                                                                                                 0.06s  
  ✓ error handling performance                                                                                                                  0.01s  

   WARN  Tests\Feature\ActorSecurityTest
  ✓ sql injection protection                                                                                                                    0.03s  
  ✓ xss protection in form                                                                                                                      0.02s  
  ✓ xss protection in submissions view                                                                                                          0.02s  
  - csrf protection → CSRF protection test skipped due to test environment setup                                                                0.01s  
  ✓ input validation prevents malicious data                                                                                                    0.02s  
  ✓ email validation prevents injection                                                                                                         0.02s  
  ✓ description validation prevents injection                                                                                                   0.03s  
  ✓ rate limiting protection                                                                                                                    0.31s  
  ✓ file upload protection                                                                                                                      0.02s  
  ✓ http method validation                                                                                                                      0.47s  
  ✓ headers security                                                                                                                            0.02s  
  ✓ sensitive data exposure                                                                                                                     0.02s  
  ✓ database constraint enforcement                                                                                                             0.01s  
  ✓ session security                                                                                                                            0.02s  
  ✓ cookie security                                                                                                                             0.02s  
  ✓ input sanitization                                                                                                                          0.02s  
  ✓ unicode and special character handling                                                                                                      0.02s  
  ✓ memory exhaustion protection                                                                                                                0.02s  
  ✓ api endpoint security                                                                                                                       0.01s  
  ✓ database query injection protection                                                                                                         0.02s  

   PASS  Tests\Feature\ActorTest
  ✓ actor form displays correctly                                                                                                               0.03s  
  ✓ actor form validation works                                                                                                                 0.02s  
  ✓ actor form requires unique email                                                                                                            0.02s  
  ✓ actor submissions page displays correctly                                                                                                   0.02s  
  ✓ api prompt validation endpoint                                                                                                              0.01s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                                                                               0.02s  

───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\ActorIntegrationTest > form validation error display                                                                        
  Expected: The email field is required

   FAILED  Tests\Feature\ActorIntegrationTest > form preserves input on validation error                                                               
  Expected: value="test@example.com"

   FAILED  Tests\Feature\ActorIntegrationTest > caching behavior                                                                                       
  Failed asserting that a row in the table [actors] matches the expected attributes.
```

---

## Summary
- **Tests:** 83  
- **Passed:** 75  
- **Failed:** 3  
- **Skipped:** 5  
- **Assertions:** 482  
- **Duration:** 3.83s  
