# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-10-04

### 🎉 Major Release - Enhanced Schema System

#### ✨ Added
- **Enhanced Blueprint Class**: 25+ advanced column types including JSON, UUID, enum, set, binary, longText
- **Foreign Key CASCADE Support**: Full ON DELETE CASCADE and ON UPDATE CASCADE for both MySQL and SQLite
- **Advanced Index Management**: Composite indexes, unique constraints, and index dropping capabilities
- **Table Modification Methods**: `dropColumn()`, `renameColumn()`, and table alteration support
- **Laravel-Compatible API**: `morphs()`, `softDeletes()`, `rememberToken()`, and other Laravel-style helpers
- **Cross-Database Compatibility**: Intelligent fallbacks for SQLite when MySQL features aren't available
- **ForeignKeyDefinition Class**: Dedicated class for managing foreign key constraints
- **Enhanced MySQL Grammar**: Integrated constraint creation in CREATE TABLE statements
- **Enhanced SQLite Grammar**: Added support for ON DELETE/UPDATE clauses in foreign keys

#### 🔧 Technical Improvements
- **Prevent Duplicate Constraints**: Modified MySQL grammar to avoid duplicate foreign key and index creation
- **Optimized Table Creation**: Foreign keys and indexes now created inline with CREATE TABLE for better performance
- **Enhanced Error Handling**: Better error messages and constraint violation handling
- **Comprehensive Testing**: Full test coverage for CASCADE DELETE operations on both databases

#### 🚀 Performance Enhancements
- **Batch Operations**: Improved support for bulk inserts and updates
- **Connection Management**: Better connection handling and pooling
- **Query Optimization**: Reduced number of ALTER TABLE statements during schema creation

#### 📚 Documentation
- **Comprehensive README**: Detailed documentation with examples and use cases
- **Code Examples**: Practical examples for all major features
- **API Documentation**: Complete method documentation and parameter explanations

#### 🧪 Testing
- **MySQL CASCADE Tests**: Complete test suite for MySQL foreign key operations
- **SQLite CASCADE Tests**: Full SQLite foreign key constraint testing
- **Cross-Database Tests**: Verification of feature parity between MySQL and SQLite
- **Performance Tests**: Benchmarking and performance validation

#### 🔄 Breaking Changes
- **Blueprint Enhancement**: Some internal Blueprint methods have changed signatures
- **Foreign Key Handling**: Foreign key creation process has been completely rewritten
- **MySQL Grammar**: Modified constraint compilation to prevent duplicates

### 🐛 Fixed
- **Duplicate Foreign Key Constraints**: Fixed MySQL creating constraints twice
- **Missing CASCADE Clauses**: Fixed SQLite not including ON DELETE/UPDATE clauses
- **Index Duplication**: Resolved duplicate index creation in MySQL
- **Cross-Database Compatibility**: Fixed inconsistencies between MySQL and SQLite implementations

## [1.0.0] - Previous Release

### Initial Release Features
- Basic query builder functionality
- Simple schema operations
- MySQL and SQLite database support
- Core CRUD operations
- Transaction support
- Basic Blueprint implementation

---

## Migration Guide from 1.0.0 to 2.0.0

### Foreign Key Definitions
**Before (1.0.0):**
```php
$table->foreign('user_id')->references('id')->on('users');
```

**After (2.0.0):**
```php
$table->foreign('user_id')
      ->references('id')
      ->on('users')
      ->onDelete('cascade')    // Now properly supported!
      ->onUpdate('cascade');   // Enhanced functionality
```

### Advanced Column Types
**New in 2.0.0:**
```php
$table->json('metadata');           // JSON support
$table->uuid('identifier');         // UUID columns
$table->enum('status', ['a', 'b']); // Enum types
$table->morphs('taggable');         // Polymorphic relations
$table->softDeletes();              // Laravel-style soft deletes
```

### Enhanced Schema Operations
**New in 2.0.0:**
```php
$schema->table('users', function ($table) {
    $table->dropColumn('old_column');
    $table->renameColumn('old_name', 'new_name');
});
```

For more detailed migration instructions, see the [README.md](README.md) file.