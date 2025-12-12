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

## 🚧 Elaboration 3 / Iteration 3:

## PART 1

### Customer-facing updates
- Added customer login/registration flow and public sidebar greeting; login link hidden when signed in.
- Made vehicle thumbnails clickable to detail view and added reservation request deep link from vehicle pages into the contact form.
- Contact form now prefills for logged-in customers, supports Reservation tag, and writes robustly to `storage/logs/contact_messages.log`.

### Messaging and reservations
- Dealer message viewer shows tags/status and reads from the contact log; dashboard now has a Recent Messages widget.
- Customer profile page added (customer-only) showing their info and their sent messages with a cancel-reservation action that logs a cancellation.
- Employee toast notifications poll for new reservation or cancellation events and pop up on any dealer page.

### Reliability / safety
- Hardened contact logging (mkdir/chmod/touch + temp fallback) so missing log files no longer block message capture.

### UI polish
- Minor dashboard refinements and quick links; profile page styling for message history.

## PART 2

### Messaging enhancements
- Added "Create Reservation" button on message.php detail view for messages tagged "Reservation"; prefills customer email and vehicle ID in add_reservation.php when available.
- Message details now show vehicle-specific action buttons (View/Edit) when vehicle data is present.

### Vehicle browsing (public shop)
- Added top/bottom pagination controls to index.php for browsing vehicle inventory; default page size is 15.
- Added per-page dropdown selector (15, 30, 45, 60, All) positioned at top-left above vehicle grid.
- Per-page selection now persists in browser localStorage, so customer's choice is remembered on return visits.
- Pagination hides when all results fit on one page.

### Reservation form improvements
- Enlarged notes textarea in add_reservation.php (rows 6, min-height 140px, 100% width) for better usability.

### Dashboard fixes and enhancements
- Fixed "Reservations Today" widget to use database-side date window (CURDATE()) instead of PHP date string, eliminating timezone-related count mismatches.
- Made "Reservations Today" KPI clickable, linking to reservations.php for quick access.

### Quality of life
- Search term and pagination now work together seamlessly on index.php; filtering first, then paginating through results.