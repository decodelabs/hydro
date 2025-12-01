# Hydro — Package Specification

> **Cluster:** `http`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/hydro`
> **Role:** HTTP client

This document describes the purpose, contracts, and design of **Hydro** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Hydro in their own applications or libraries.
- Contributors **maintaining or extending** Hydro.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Hydro provides a simple PSR-18 HTTP client wrapper around Guzzle, offering a convenient interface to common HTTP client functionality. It extends the PSR-18 ClientInterface with helper methods for fetching resources as different types (files, strings, JSON, Trees) and integrates seamlessly with Atlas for file handling and Collections for JSON tree structures. Hydro simplifies HTTP requests by providing high-level methods that handle common use cases while maintaining full PSR-18 compliance and error handling capabilities.

### 1.2 Non-Goals

Hydro does **not**:

- Provide a full-featured HTTP client framework (wraps Guzzle for core functionality)
- Handle HTTP server functionality (see `decodelabs/harvest` for server-side HTTP)
- Provide routing or URL generation (see `decodelabs/greenleaf` for routing)
- Manage authentication or OAuth flows (handled via request options)
- Provide caching mechanisms (see other packages for caching)
- Handle WebSocket connections

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `http` (see Chorus taxonomy)
- Hydro is a mid-level HTTP client package that provides a Decode Labs-friendly interface to PSR-18 HTTP client functionality. It sits above Guzzle (the underlying implementation) and integrates with Atlas (file handling) and Collections (data structures). It's used by packages like Scrutiny (captcha) and Imprint (PDF generation) for making HTTP requests.

### 2.2 Typical Usage Contexts

Typical places Hydro appears:

- Fetching remote resources (files, JSON, text)
- Making API requests to external services
- Downloading files from URLs
- Consuming REST APIs
- Integration with third-party HTTP services

Hydro is intended to be used whenever you need to make HTTP requests and want a simple, Decode Labs-integrated interface that works seamlessly with Atlas files and Collections data structures.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Hydro`
  The main service class implementing the Client interface. Provides all HTTP client methods and integrates with Kingdom for service resolution.

- `DecodeLabs\Hydro\Client`
  Interface extending PSR-18 ClientInterface with additional convenience methods for fetching resources in various formats.

- `DecodeLabs\Hydro\Client\Guzzle`
  Guzzle-based implementation of the Client interface, providing the actual HTTP request execution.

- `DecodeLabs\Hydro\ClientTrait`
  Trait providing default implementations of convenience methods (get, getString, getFile, etc.) that can be used by Client implementations.

- `DecodeLabs\Hydro\ClientExceptionTrait`
  Trait for exceptions that provides access to the associated RequestInterface instance.

### 3.2 Main Entry Points

The main usage pattern is fetching resources via the Hydro service:

```php
use DecodeLabs\Hydro;
use DecodeLabs\Monarch;

$hydro = Monarch::getService(Hydro::class);

$file = $hydro->get('https://example.com/file.txt'); // Atlas MemoryFile
$string = $hydro->getString('https://example.com/file.txt'); // String
$localFile = $hydro->getFile('https://example.com/file.txt', '/path/to/save.txt'); // LocalFile
$json = $hydro->getJson('https://example.com/api/data.json'); // Decoded JSON
$tree = $hydro->getJsonTree('https://example.com/api/data.json'); // Collections Tree
```

For custom requests with options:

```php
$response = $hydro->request('GET', [
    'url' => 'https://example.com/api',
    'timeout' => 10,
    'headers' => ['Authorization' => 'Bearer token']
]);
```

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/atlas`
  File handling for downloading and saving HTTP responses as files.

- `decodelabs/coercion`
  Type coercion utilities for URL and option handling.

- `decodelabs/exceptional`
  Enhanced exception handling throughout the package.

- `decodelabs/kingdom`
  Service container integration for service resolution.

**Optional integration:**

- `decodelabs/collections` (optional)
  Detected at runtime if installed, used for `getJsonTree()` method to return Tree instances instead of plain arrays.

### 4.2 External Dependencies

- `psr/http-client` (^1.0.3)
  PSR-18 HTTP client interface specification.

- `psr/http-message` (^2.0)
  PSR-7 HTTP message interfaces.

- `guzzlehttp/guzzle` (^7.9.3)
  Guzzle HTTP client library used as the underlying implementation.

See `composer.json` for supported PHP versions.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- All HTTP methods return appropriate types (File, string, array, Tree, ResponseInterface)
- URLs can be provided as strings or arrays (with 'url' key and additional options)
- Error responses (4xx, 5xx) are handled according to the 'throw' option and onFailure callback
- File downloads respect Content-Disposition headers for filename extraction
- JSON responses are decoded using `json_decode()` with associative arrays
- Stream transfers use 8192-byte chunks for efficient memory usage
- All methods that accept URLs support both string URLs and option arrays

### 5.2 Input & Output Contracts

**Hydro::get(string|array url, ?Closure onFailure = null):**
- **Input:** URL as string or array with 'url' key, optional failure callback
- **Output:** Atlas MemoryFile containing response body
- **Preconditions:** URL must be valid, Guzzle must be installed
- **Postconditions:** File is ready for reading, positioned at start

**Hydro::getString(string|array url, ?Closure onFailure = null):**
- **Input:** URL as string or array, optional failure callback
- **Output:** String containing response body
- **Preconditions:** URL must be valid
- **Postconditions:** String contains complete response body

**Hydro::getFile(string|array url, string|LocalFile|LocalDir path, ?Closure onFailure = null):**
- **Input:** URL, destination path (file, directory, or string), optional failure callback
- **Output:** Atlas LocalFile containing downloaded content
- **Preconditions:** URL must be valid, path must be writable
- **Postconditions:** File is saved and closed, filename determined from Content-Disposition or URL path

**Hydro::getJson(string|array url, ?Closure onFailure = null):**
- **Input:** URL as string or array, optional failure callback
- **Output:** Decoded JSON as string|int|float|bool|array|null
- **Preconditions:** URL must be valid, response must be valid JSON
- **Postconditions:** JSON is decoded and returned

**Hydro::getJsonTree(string|array url, ?Closure onFailure = null):**
- **Input:** URL as string or array, optional failure callback
- **Output:** Collections Tree instance
- **Preconditions:** URL must be valid, Collections package must be installed, response must be valid JSON
- **Postconditions:** JSON is decoded and wrapped in Tree instance

**Hydro::request(string method, string|array url, ?Closure onFailure = null):**
- **Input:** HTTP method, URL as string or array, optional failure callback
- **Output:** PSR-7 ResponseInterface
- **Preconditions:** Method and URL must be valid
- **Postconditions:** Response is returned (may be error response if throw=false)

---

## 6. Error Handling

### 6.1 Exception Types

Hydro throws Exceptional exceptions that implement PSR-18 exception interfaces:

- `Exceptional::Client`: For 4xx and 5xx HTTP responses (implements ClientExceptionInterface)
- `Exceptional::Network`: For network/connection errors (implements NetworkExceptionInterface)
- `Exceptional::Request`: For invalid request errors (implements RequestExceptionInterface)
- `Exceptional::ComponentUnavailable`: When Guzzle or Collections are required but not installed
- `Exceptional::InvalidArgument`: When URL is missing or invalid
- `Exceptional::Runtime`: When failure callback returns invalid type

All exceptions use the Exceptional pattern and include ClientExceptionTrait for accessing the associated RequestInterface.

### 6.2 Error Strategy

Hydro uses a flexible error handling strategy:

- By default, HTTP error responses (4xx, 5xx) throw exceptions when `throw` option is true (default)
- When `throw` is false, error responses are returned as normal ResponseInterface instances
- The `onFailure` callback allows custom error handling, including retrying with different URLs or throwing custom exceptions
- Network errors always throw exceptions (cannot be suppressed)
- The failure callback must return a ResponseInterface or throw an exception

---

## 7. Configuration & Extensibility

### 7.1 Configuration

No runtime configuration is required. Hydro works out of the box with Guzzle defaults. Configuration is done through request options:

- `timeout`: Request timeout in seconds
- `headers`: Custom HTTP headers
- `throw`: Whether to throw exceptions on error responses (default: true)
- All standard Guzzle request options are supported

### 7.2 Extension Points

Hydro supports extension via:

- **Custom Client implementations:** Implement `DecodeLabs\Hydro\Client` interface to use different HTTP client backends
- **ClientTrait usage:** Use ClientTrait in custom implementations to get convenience methods
- **Failure callbacks:** Provide Closure callbacks for custom error handling and retry logic
- **Request options:** Pass any Guzzle-compatible options for advanced configuration

---

## 8. Interactions with Other Packages

Hydro is designed to integrate with:

- **`decodelabs/atlas`**
  Uses Atlas for file handling. Responses can be saved as LocalFile or MemoryFile instances.

- **`decodelabs/collections`** (optional)
  Uses Collections Tree for structured JSON data when available.

- **`decodelabs/scrutiny`**
  Uses Hydro for making HTTP requests to captcha services.

- **`decodelabs/imprint`**
  Uses Hydro for making HTTP requests to PDF generation services.

- **`guzzlehttp/guzzle`**
  Uses Guzzle as the underlying PSR-18 HTTP client implementation.

Design assumptions:

- Atlas is available for file operations
- Guzzle is installed and available
- Collections is optional but recommended for JSON tree operations
- Kingdom service container is available for service resolution

---

## 9. Usage Examples

### 9.1 Basic File Download

```php
use DecodeLabs\Hydro;
use DecodeLabs\Monarch;

$hydro = Monarch::getService(Hydro::class);

// Download to memory
$file = $hydro->get('https://example.com/file.txt');

// Download to local file
$localFile = $hydro->getFile(
    'https://example.com/file.txt',
    '/path/to/save.txt'
);

// Download to directory (filename from Content-Disposition or URL)
$localFile = $hydro->getFile(
    'https://example.com/file.txt',
    '/path/to/downloads/'
);
```

### 9.2 JSON API Requests

```php
use DecodeLabs\Hydro;
use DecodeLabs\Monarch;

$hydro = Monarch::getService(Hydro::class);

// Get JSON as array
$data = $hydro->getJson('https://api.example.com/data.json');

// Get JSON as Tree (requires Collections)
$tree = $hydro->getJsonTree('https://api.example.com/data.json');
```

### 9.3 Custom Request Options

```php
use DecodeLabs\Hydro;
use DecodeLabs\Monarch;

$hydro = Monarch::getService(Hydro::class);

$response = $hydro->request('POST', [
    'url' => 'https://api.example.com/endpoint',
    'timeout' => 30,
    'headers' => [
        'Authorization' => 'Bearer token',
        'Content-Type' => 'application/json'
    ],
    'json' => ['key' => 'value']
]);
```

### 9.4 Error Handling with Callbacks

```php
use DecodeLabs\Hydro;
use DecodeLabs\Exceptional;
use DecodeLabs\Monarch;

$hydro = Monarch::getService(Hydro::class);

$file = $hydro->get('https://example.com/file.txt', function($response) use ($hydro) {
    switch($response->getStatusCode()) {
        case 404:
            throw Exceptional::NotFound('File not found');
        
        case 500:
            // Retry with different URL
            return $hydro->request('GET', 'https://backup.example.com/file.txt');
        
        default:
            throw Exceptional::Runtime('Unexpected error');
    }
});
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Hydro:

- Uses Guzzle as the underlying PSR-18 client implementation
- Provides ClientTrait with default implementations of convenience methods
- Integrates with Atlas for file operations (MemoryFile, LocalFile)
- Handles URL/option array parsing to support both string URLs and option arrays
- Converts Guzzle exceptions to Exceptional exceptions with proper PSR-18 interface implementation
- Supports failure callbacks for custom error handling
- Uses stream chunking (8192 bytes) for efficient file transfers

Contributors should:

- Maintain PSR-18 compliance in all Client implementations
- Preserve Atlas integration for file operations
- Keep exception handling consistent with Exceptional pattern
- Support both string URLs and option arrays for flexibility
- Ensure proper stream handling for large file downloads

### 10.2 Performance Considerations

- Stream transfers use 8192-byte chunks to balance memory usage and performance
- MemoryFile instances are used for in-memory responses to avoid temporary file creation
- File downloads write directly to destination without intermediate buffering
- JSON decoding happens in-memory, which may be limiting for very large JSON responses
- Guzzle connection pooling and other optimizations are available via request options

### 10.3 Gotchas & Historical Decisions

- **URL/Options array:** The dual format (string URL or array with 'url' key) allows passing Guzzle options directly, but the 'url' key is extracted and removed from options
- **Error response handling:** By default, error responses throw exceptions, but this can be controlled via the 'throw' option or onFailure callback
- **File naming:** When downloading to a directory, the filename is extracted from Content-Disposition header or URL path, which may not always be reliable
- **JSON Tree requirement:** `getJsonTree()` requires Collections package, but this is only checked at runtime, not as a hard dependency
- **Guzzle dependency:** Guzzle is a hard requirement but the package could theoretically support other PSR-18 implementations

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- All convenience methods (get, getString, getFile, getTempFile, getJson, getJsonTree)
- URL handling (string URLs and option arrays)
- Error response handling (4xx, 5xx) with and without throw option
- Failure callback functionality
- File download with various destination types (file, directory, string path)
- Filename extraction from Content-Disposition headers
- JSON decoding and Tree conversion
- Stream transfer functionality
- Network error handling
- Request option passing to Guzzle
- Service resolution via Kingdom

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** 1.5
- **Readme:** 1
- **Docs:** 0
- **Tests:** 0

Hydro is an early-stage package with basic functionality. The code quality is functional but minimal, and the README provides basic usage examples. Comprehensive documentation (this spec) and test coverage are planned but not yet implemented. The package serves its purpose as a simple HTTP client wrapper but may be expanded and refined in the future.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Comprehensive test suite covering all HTTP methods and error scenarios
- Additional convenience methods for common HTTP operations (POST, PUT, DELETE with JSON)
- Request/response middleware support
- Connection pooling configuration
- Retry logic with exponential backoff
- Request/response logging and debugging tools
- Cookie jar support
- HTTP/2 support configuration
- Performance optimizations for large file downloads
- Enhanced error messages with request/response context

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/atlas` (file handling)
  - `decodelabs/collections` (optional JSON tree support)
  - `decodelabs/scrutiny` (uses Hydro for captcha requests)
  - `decodelabs/imprint` (uses Hydro for PDF service requests)

- **Standards:**
  - PSR-7: HTTP message interfaces
  - PSR-18: HTTP client interfaces

- **External libraries:**
  - Guzzle: HTTP client library

- **Repository:**
  - `https://github.com/decodelabs/hydro`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

