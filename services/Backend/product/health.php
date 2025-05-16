<?php
// Simple health check file at the document root
header('Content-Type: application/json');
echo json_encode(['status' => 'UP']);