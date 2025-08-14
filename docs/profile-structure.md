# Profile Structure Documentation

## Overview

This document describes the new unified profile structure implemented in the application. The new structure simplifies the profile management by consolidating multiple profile types into a single, extensible structure.

## Previous Structure

Previously, the application had multiple profile tables:

- `users` - Basic user information and authentication
- `professional_profiles` - Profiles for professional users
- `freelance_profiles` - An older version of professional profiles
- `client_profiles` - Profiles for client users
- `company_profiles` - An older version of client profiles for enterprise clients

This structure led to:
- Redundancy and duplication of data
- Complexity in maintaining multiple profile types
- Confusion with overlapping functionality

## New Structure

The new structure consists of:

1. **User Table** (unchanged)
   - Basic authentication info (email, password)
   - Common fields (first_name, last_name)
   - The `is_professional` flag to distinguish user types

2. **Profile Table** (new consolidated table)
   - Common profile fields for all users
   - One-to-one relationship with User
   - Fields: user_id, phone, address, city, country, bio, avatar, social_links, completion_percentage

3. **ProfessionalDetail Table**
   - Professional-specific fields only
   - One-to-one relationship with Profile
   - Fields: profession, expertise, years_of_experience, hourly_rate, availability_status, skills, portfolio, etc.

4. **ClientDetail Table**
   - Client-specific fields only
   - One-to-one relationship with Profile
   - Fields: type (particulier/entreprise), company_name, industry, company_size, etc.

## Benefits of the New Structure

- **Reduced Redundancy**: Common fields are stored in a single place
- **Simplified Queries**: Easier to retrieve and update profile data
- **Better Maintainability**: Changes to common fields only need to be made in one place
- **Extensibility**: Easier to add new profile types or fields in the future
- **Cleaner Code**: More logical organization of profile-related functionality

## API Endpoints

The new profile structure is accessible through the following API endpoints:

- `GET /api/profile/new` - Get the profile of the authenticated user
- `PUT /api/profile/new` - Update the profile of the authenticated user
- `POST /api/profile/new/complete` - Complete the profile (first login)
- `GET /api/profile/new/completion` - Get the profile completion status
- `POST /api/profile/new/avatar` - Upload avatar
- `POST /api/profile/new/portfolio` - Upload portfolio items (professional only)
- `DELETE /api/profile/new/portfolio/{id}` - Delete a portfolio item (professional only)
- `PUT /api/profile/new/availability` - Update availability status (professional only)

## Migration Path

The migration to the new profile structure is handled in several steps:

1. Create new tables: `profiles`, `professional_details`, and `client_details`
2. Migrate data from old tables to new tables
3. Update code to use the new structure
4. Test thoroughly to ensure all functionality works correctly
5. Once confirmed, drop the old tables

## Backward Compatibility

For backward compatibility, the User model maintains the old relationship methods:

- `professionalProfile()` - Now deprecated, use `profile()->professionalDetails()` instead
- `clientProfile()` - Now deprecated, use `profile()->clientDetails()` instead
- `freelanceProfile()` - Now deprecated, use `profile()->professionalDetails()` instead
- `companyProfile()` - Now deprecated, use `profile()->clientDetails()` instead

These methods will continue to work during the transition period but will be removed in a future update.

## Implementation Details

### Models

- `Profile` - Represents the common profile data for all users
- `ProfessionalDetail` - Contains professional-specific data
- `ClientDetail` - Contains client-specific data

### Services

- `ProfileService` - Handles profile-related operations like retrieval, updates, and calculations

### Controllers

- `NewProfileController` - Handles API requests for the new profile structure

## Future Improvements

- Remove deprecated methods once all code has been updated to use the new structure
- Enhance profile completion calculation to be more granular
- Add more profile-specific features based on user type
