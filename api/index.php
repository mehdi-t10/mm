<?php
/**
 * API Router - Redirects old API calls to new organized structure
 * This file routes old-style API calls to new organized API structure
 */

$request = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $request);

// Extract the actual filename from the path
$fileName = end($parts);

// Map old API calls to new organized structure
$apiMap = [
    // Auth
    'login.php' => 'auth/login.php',
    'register.php' => 'auth/register.php',
    'forgot_password.php' => 'auth/forgot_password.php',
    
    // Reservations
    'reservation_request.php' => 'reservations/reservation_request.php',
    'reserve.php' => 'reservations/reserve.php',
    'admin_validate_reservation.php' => 'reservations/admin_validate_reservation.php',
    'admin_validate_reservation_with_room.php' => 'reservations/admin_validate_reservation_with_room.php',
    'admin_reject_reservation.php' => 'reservations/admin_reject_reservation.php',
    'admin_list_reservations.php' => 'reservations/admin_list_reservations.php',
    'get_my_reservations.php' => 'reservations/get_my_reservations.php',
    
    // Rooms
    'get_available_rooms.php' => 'rooms/get_available_rooms.php',
    'get_room_calendar.php' => 'rooms/get_room_calendar.php',
    'assign_room_number.php' => 'rooms/assign_room_number.php',
    'auto_assign_rooms.php' => 'rooms/auto_assign_rooms.php',
    
    // Admin
    'admin_login.php' => 'admin/admin_login.php',
    'admin_register.php' => 'admin/admin_register.php',
    'admin_delete_client.php' => 'admin/admin_delete_client.php',
    'admin_day_requests.php' => 'admin/admin_day_requests.php',
    'admin_plan_activity.php' => 'admin/admin_plan_activity.php',
    'admin_set_deposit.php' => 'admin/admin_set_deposit.php',
    'admin_set_discount.php' => 'admin/admin_set_discount.php',
    'admin_email_config.php' => 'admin/admin_email_config.php',
    
    // Billing
    'invoice.php' => 'billing/invoice.php',
    'get_invoice.php' => 'billing/get_invoice.php',
    'send_invoice.php' => 'billing/send_invoice.php',
    'send_invoice_email.php' => 'billing/send_invoice_email.php',
    'get_payment_status.php' => 'billing/get_payment_status.php',
    'record_payment.php' => 'billing/record_payment.php',
    
    // Services
    'add_service.php' => 'services/add_service.php',
    'get_available_facilities.php' => 'services/get_available_facilities.php',
    'get_facilities_for_activities.php' => 'services/get_facilities_for_activities.php',
    
    // Email
    'send_welcome_email.php' => 'email/send_welcome_email.php',
    'email_logs.php' => 'email/email_logs.php',
];

if (isset($apiMap[$fileName])) {
    require_once __DIR__ . '/' . $apiMap[$fileName];
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
}
?>

