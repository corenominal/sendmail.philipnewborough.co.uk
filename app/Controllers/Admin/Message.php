<?php

namespace App\Controllers\Admin;

use Hermawan\DataTables\DataTable;
use App\Models\MessageModel;

class Message extends BaseController
{
    /**
     * Return quick stats as JSON.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function stats()
    {
        $model = new MessageModel();
        $db    = \Config\Database::connect();

        return $this->response->setJSON([
            'pending'    => $model->where('sent_at IS NULL')->countAllResults(),
            'sent_today' => (int) $db->table('messages')
                                ->where('DATE(sent_at)', date('Y-m-d'))
                                ->where('deleted_at IS NULL')
                                ->countAllResults(),
            'sent_month' => (int) $db->table('messages')
                                ->where('YEAR(sent_at)', date('Y'))
                                ->where('MONTH(sent_at)', date('m'))
                                ->where('deleted_at IS NULL')
                                ->countAllResults(),
            'total'      => (int) $db->table('messages')
                                ->where('deleted_at IS NULL')
                                ->countAllResults(),
        ]);
    }

    /**
     * DataTables server-side processing endpoint for the messages queue.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function datatableData()
    {
        $filter = $this->request->getGet('filter');

        $model = new MessageModel();
        $model->select('id, uuid, `from`, `to`, subject, mailtype, created_at, sent_at')
              ->where('deleted_at IS NULL');

        if ($filter === 'pending') {
            $model->where('sent_at IS NULL');
        } elseif ($filter === 'sent') {
            $model->where('sent_at IS NOT NULL');
        }

        return DataTable::of($model)
            ->add('actions', function ($row) {
                $id = (int) $row->id;
                return '<div class="btn-group btn-group-sm" role="group">'
                     . '<button type="button" class="btn btn-outline-primary btn-view-message" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View"><i class="bi bi-eye"></i></button>'
                     . '<button type="button" class="btn btn-outline-primary btn-resend-message" data-id="' . $id . '"' . ($row->sent_at ? '' : ' disabled') . ' data-bs-toggle="tooltip" data-bs-placement="bottom" title="Resend"><i class="bi bi-send"></i></button>'
                     . '<button type="button" class="btn btn-outline-primary btn-delete-message" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><i class="bi bi-trash"></i></button>'
                     . '</div>';
            }, 'last')
            ->toJson(true);
    }

    /**
     * Return a single message as JSON for the view modal.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function get(int $id)
    {
        $model   = new MessageModel();
        $message = $model->find($id);

        if (!$message) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Message not found']);
        }

        return $this->response->setJSON($message);
    }

    /**
     * Soft-delete a message by ID.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete(int $id)
    {
        $model   = new MessageModel();
        $message = $model->find($id);

        if (!$message) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Message not found']);
        }

        if (!$model->delete($id)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to delete message']);
        }

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Insert a new copy of the message into the queue.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function resend(int $id)
    {
        $model   = new MessageModel();
        $message = $model->find($id);

        if (!$message) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Message not found']);
        }

        $data = [
            'from'     => $message['from'],
            'to'       => $message['to'],
            'cc'       => $message['cc']       ?? null,
            'bcc'      => $message['bcc']      ?? null,
            'subject'  => $message['subject'],
            'body'     => $message['body'],
            'mailtype' => $message['mailtype'],
            'domain'   => $message['domain']   ?? null,
        ];

        if (!$model->insert($data)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to queue resend']);
        }

        return $this->response->setJSON(['success' => true]);
    }
}
