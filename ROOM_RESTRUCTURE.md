# Room Management Restructuring - Documentation

## Overview

The room management system has been completely restructured from 3 individual rooms to a 3-tier room type system with dynamic availability calculation.

**Old System:**

- 3 rooms: Chambre A, B, C
- Individual occupied/capacity tracking
- Fixed availability per room

**New System:**

- 3 room types: Simple (100), Double (50), Triple (50) = 200 total
- Dynamic availability calculation based on validated reservations
- Type-based selection and management

## Data Structure Changes

### rooms.json Format

```json
{
  "id": 1,
  "name": "Chambre Simple",
  "type": "simple",
  "capacity": 1,
  "total": 100,
  "available": 100,
  "price_per_night": 60,
  "description": "Chambre confortable pour 1 personne"
}
```

### Reservation Structure (New Fields)

```json
{
  "id": 1,
  "room_type": "simple",           // NEW: Type of room reserved
  "room_id": 1,                     // NEW: Maps to type ID in rooms.json
  "room": "Chambre Simple",         // LEGACY: Kept for compatibility
  "status": "validee",
  ...
}
```

## Files Modified

### Frontend Files

1. **admin-dashboard.html**
   - Updated `loadRooms()`: Uses new room structure
   - Updated `updateRoomOccupancy()`: Displays room types with dynamic counts
   - Shows total/reserved/available per type

2. **client-dashboard.html**
   - Updated `selectRoom()`: Stores both room_id and room_type
   - Updated `handleReservation()`: Sends room_type to API

### API Endpoints

1. **api/get_available_rooms.php**
   - Returns available room types based on date range
   - Counts validated reservations by type
   - Backward compatible with old format mapping

2. **api/reserve.php**
   - Now stores room_type and room_id fields
   - Accepts room selection from client-dashboard

3. **api/reservation_request.php**
   - Supports room_type and room_id in new reservations

4. **api/admin_validate_reservation.php**
   - Validates and assigns room_type to reservation
   - Counts existing validée reservations to check availability
   - No longer modifies room.occupied (deprecated)

5. **api/admin_validate_reservation_with_room.php**
   - Admin can specify room_type when validating
   - Counts reservations by type for availability

## Backward Compatibility

The system maintains backward compatibility with old reservation data:

**Old Format Mapping:**

- "Chambre C" → type: "simple"
- "Chambre B" or "Chambre A" → type: "double"

This mapping is implemented in:

- `admin-dashboard.html` (updateRoomOccupancy)
- `api/get_available_rooms.php`
- Existing reservations continue to work

## Availability Calculation

**Old Method:** Check `room.occupied < room.capacity`

**New Method:**

```javascript
reserved_count = Count of reservations where:
  - status === 'validee'
  - room_type === target_type
  - dates overlap

available = room_type.total - reserved_count
```

## Testing the System

### 1. Landing Page Reservation

1. Fill out reservation form with dates
2. Submit from index.html
3. Should create reservation without room_type initially

### 2. Client Dashboard Reservation

1. Login as client
2. Select dates and number of people
3. Should see 3 room type options with pricing
4. Select one and complete reservation
5. Reservation should store room_type

### 3. Admin Validation

1. Admin dashboard should show 3 room types
2. View pending reservations
3. Validate reservation (should assign room_type if not present)
4. Check availability counts update correctly

### 4. Occupancy Display

Admin dashboard should show:

- Chambre Simple: Total 100, Reserved X, Available 100-X, Y% occupied
- Chambre Double: Total 50, Reserved X, Available 50-X, Y% occupied
- Chambre Triple: Total 50, Reserved X, Available 50-X, Y% occupied

## Migration Notes

### Existing Reservations

- Automatically mapped when accessed (no DB migration needed)
- "Chambre C" data treated as "simple"
- "Chambre B"/"Chambre A" data treated as "double"

### Database/File Consistency

- rooms.json: Updated to new structure ✓
- reservations.json: Old format works (auto-mapped) ✓
- New reservations created with room_type field ✓

## Future Improvements

1. Clean up old reservation mapping (migration script to update all reservations)
2. Remove "available" field from rooms.json (not used - dynamic)
3. Add room type management UI for admins
4. Add ability to modify room type quotas (100/50/50)
5. Add room type preferences in booking form

## Troubleshooting

**Issue: Room selection not working**

- Check browser console for AJAX errors
- Verify get_available_rooms.php returns data
- Check localStorage for currentUser (must be logged in for client-dashboard)

**Issue: Admin validation fails**

- Verify reserve.php was updated to accept room_type
- Check admin_validate_reservation.php receives room_type
- Verify rooms.json has correct structure

**Issue: Occupancy display incorrect**

- Check that reservations have status = 'validee'
- Verify room_type field is set in reservations
- Check reservation date ranges don't have typos

## Support

For questions or issues, check:

1. Browser developer console (F12) for JavaScript errors
2. Network tab to see API responses
3. PHP error logs if APIs return errors
4. reservations.json data integrity
