# Changelog

All notable changes to this project will be documented in this file.

## [2.0.1] - 2025-10-05

### Added
- **Advanced Relationship Methods**
  - `hasOneThrough()` - Define has-one-through relationships
  - `hasManyThrough()` - Define has-many-through relationships  
  - `morphOne()` - Define polymorphic one-to-one relationships
  - `morphMany()` - Define polymorphic one-to-many relationships
  - `morphTo()` - Define polymorphic inverse relationships
  - `morphToMany()` - Define many-to-many polymorphic relationships
  - `morphedByMany()` - Define polymorphic many-to-many inverse relationships

### Enhanced
- **Model Class Improvements**
  - Fixed `__callStatic()` to properly delegate to query builder
  - Improved `guessBelongsToRelation()` method for better relationship detection
  - Added comprehensive relationship method instantiation
  - Enhanced helper functions for relationship management

### Fixed
- **Helper Functions**
  - Added `str_plural()` helper function for automatic pluralization
  - Moved helper functions to proper location in helpers.php
  - Fixed relationship method signatures and return types

### Technical Details
- Laravel-compatible relationship API
- Polymorphic relationship support foundation
- Through relationship support foundation  
- Improved code organization and documentation

## [2.0.0] - 2025-10-04

### Added
- **Enhanced Schema Builder** with 25+ advanced column types
- **Foreign Key CASCADE** support for DELETE/UPDATE operations
- **Cross-Database Compatibility** between MySQL and SQLite
- **Advanced Column Types** including JSON, UUID, enum, set, binary, longText
- **Index Management** with composite indexes and unique constraints
- **Laravel-Compatible API** with morphs(), softDeletes(), rememberToken()
- **Table Modification Methods** for altering existing table structures

### Enhanced
- **Blueprint Class** completely redesigned with advanced features
- **Grammar Classes** with intelligent SQL generation
- **Connection Management** with improved error handling
- **Query Builder** with enhanced functionality

### Fixed
- Cross-database foreign key implementation
- Schema generation for different database engines
- Column type fallbacks for unsupported features

## [1.0.0] - Initial Release

### Added
- Basic database abstraction layer
- Simple ORM functionality  
- Basic schema building
- MySQL and SQLite support
