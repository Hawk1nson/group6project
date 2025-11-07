## UPDATE_LOG.md

# ICS 370 - Fall 2025
## Metropolitan State University
## Instructor: Ismail Bile Hassan

## Group Project
# CDMS (Car Dealership Management System)

#### Group 6
- Andrew Hawkinson
- Jimmy Taiwo
- Xamong Thao

##### Includes summary of changes made for each step of the application development process

## 🚧 Elaboration 2 / Iteration 2:

### UI / Styling
- centralized CSS and moved all inline styles into style.css .
- layout polish to help with overall look and feel and improve readability.
- consistent page headers and action buttons across all pages.
----------
----------
### New Features
#### Reservations
- added 'add_reservation.php' and connected it to 'reservations.php' to schedule new reservations. User gets confirmation of successful addition when redirected to dashboard.

#### Vehicle Management
- new vehicle creation page 'vehicle_add.php' .
- updated 'vehicle_edit.php' with prefilled form and update-on-submit.
- Image upload for vehicles (stored in /cdms/images/vehicles/), replace image on update (only one image per vehicle at this time), save filename to DB.
- VIN uniqueness validation on add with friendly error if duplicate is found.
- Added "Add Vehicle" button to the 'vehicles.php' page.
----------
----------
### Sales workflow
#### New Sale page - 'new_sale.php added
- Live vehicle details/asking price and customer card display (fetched via 'get_vehicle.php' / 'get_customer.php').
- Confirmation prompt before finalizing.
- Friendly success banner on redirect to dashboard.
#### Reliability improvement
- added handling for duplicate sale attempts (unique constraint on one sale per vehicle). If a race occurs, the user now sees a clear message to pick another vehicle instead of a DB error.
    NOTE: THIS ISSUE NEEDS TO BE FIXED BY REVERTING status BACK TO AVAILABLE WHEN SALE IS DELETED
__________
__________
### Data / API fixes
- 'get_customer.php' : fixed connection/columns to return full customer details used by the New Sale page (including address lines).
- Selected Customer card now shows 'address_line1' / 'address_line2' and city/state/postal so sales can verify exact address.
__________
__________
### Access control and safety
- Restricted vehicle deletion to users with role 'admin' or 'manager' (both hidden in UI and enforced server-side).
- Added delete success banner on 'sales.php' after a sale deletion redirect.
__________
__________
### Usability additions
- Customers: added a primary "Add Customer" button to 'customers.php' .
- Fixed broken edit link in 'customers.php' to redirect correctly.
__________
__________
### Housekeeping and cohesion
- Global stylesheet usage verified via 'header.php' .
- Removed per-page <style></style> blocks in favor of centralized 'style.css' .
__________
__________
### Quick QA status
- Nearly all functions should be working correctly.  Will continue to search for bugs and document fixes.