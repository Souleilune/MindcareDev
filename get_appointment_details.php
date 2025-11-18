<?php
session_start();
require_once 'supabase.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get appointment ID from request
$appointment_id = $_GET['id'] ?? null;

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
    exit;
}

try {
    $user_id = $_SESSION['user']['id'];
    
    // Fetch appointment details with specialist information using supabaseSelect
    $appointments = supabaseSelect(
        'appointments',
        ['id' => $appointment_id, 'user_id' => $user_id],
        'id,user_id,specialist_id,appointment_date,appointment_time,status,notes,created_at,users!appointments_specialist_id_fkey(fullname,email,role)',
        null,
        1,
        true  // Use SERVICE_KEY to bypass RLS
    );
    
    if (empty($appointments)) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }
    
    $appointment = $appointments[0];
    
    // Format the response
    $formatted_appointment = [
        'id' => $appointment['id'],
        'specialist_name' => $appointment['users']['fullname'] ?? 'N/A',
        'specialist_email' => $appointment['users']['email'] ?? null,
        'specialist_role' => ($appointment['users']['role'] ?? 'specialist') === 'Psychologist' ? 'Psychologist' : 'Psychiatrist',
        'date' => date('F j, Y', strtotime($appointment['appointment_date'])),
        'time' => date('g:i A', strtotime($appointment['appointment_time'])),
        'status' => $appointment['status'] ?? 'Pending',
        'notes' => $appointment['notes'] ?? '',
        'created_at' => date('F j, Y g:i A', strtotime($appointment['created_at']))
    ];
    
    echo json_encode([
        'success' => true,
        'appointment' => $formatted_appointment
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching appointment details: ' . $e->getMessage()
    ]);
}
?>