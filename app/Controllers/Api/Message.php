<?php

namespace App\Controllers\Api;

use Ramsey\Uuid\Uuid;

/**
 * Handle POST request to create a new email message
 *
 * Validates the incoming JSON request and creates a new message record in the database.
 * The endpoint expects a JSON payload with email details including sender, recipient,
 * subject, body, and mail type.
 *
 * Required fields in JSON payload:
 * - from: Valid email address of the sender
 * - to: Valid email address of the recipient
 * - subject: String containing the email subject
 * - body: String containing the email body content
 * - mailtype: Either 'text' or 'html'
 *
 * Optional fields:
 * - cc: Valid email address for carbon copy
 * - bcc: Valid email address for blind carbon copy
 *
 * @return \CodeIgniter\HTTP\ResponseInterface JSON response with created message or error
 * 
 * @throws \Exception If JSON parsing fails
 * 
 * Response codes:
 * - 201: Message successfully created, returns the created message object with UUID
 * - 400: Bad request (invalid JSON, missing fields, invalid email addresses, invalid mailtype)
 * - 500: Server error (database insertion or retrieval failed)
 */
class Message extends BaseController
{
    public function index()
    {
        // Validate the request is json
        if (!$this->request->is('json')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }
        // Try to get the request body as JSON, catch any errors
        try {
            $requestBody = $this->request->getJSON(true);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid JSON']);
        } 
        
        // Validate the required fields
        $requiredFields = ['from', 'to', 'subject', 'body', 'mailtype'];
        // Check if all required fields are present
        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->setStatusCode(400)->setJSON(['error' => "Missing required field: $field"]);
            }
        }
        // Check if the mailtype is valid
        $validMailTypes = ['text', 'html'];
        if (!in_array($requestBody['mailtype'], $validMailTypes)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid mailtype']);
        }
        // Check if the from field is a valid email
        if (!filter_var($requestBody['from'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid From email address']);
        }
        // Check if the to field is a valid email
        if (!filter_var($requestBody['to'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid To email address']);
        }
        // Check if the cc field is a valid email
        if (isset($requestBody['cc']) && !filter_var($requestBody['cc'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid CC email address']);
        }
        // Check if the bcc field is a valid email
        if (isset($requestBody['bcc']) && !filter_var($requestBody['bcc'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid BCC email address']);
        }
        // Check if the subject field is a valid string
        if (!is_string($requestBody['subject'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid subject']);
        }
        // Check if the body field is a valid string
        if (!is_string($requestBody['body'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid body']);
        }

        // Create uuid
        $requestBody['uuid'] = Uuid::uuid4()->toString();
        // Load the model
        $model = model('MessageModel');
        // Insert the message
        $result = $model->insert($requestBody);
        // Check if the insert was successful
        if ($result === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert message']);
        }
        // Get the inserted message
        $message = $model->find($model->insertID());
        // Check if the message was found
        if ($message === null) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to retrieve message']);
        }
        // Return the message
        return $this->response->setStatusCode(201)->setJSON($message);
    }
}