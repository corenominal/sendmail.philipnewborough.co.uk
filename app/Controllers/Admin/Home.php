<?php

namespace App\Controllers\Admin;

use App\Models\MessageModel;

class Home extends BaseController
{
    /**
     * Display the Admin Dashboard page.
     *
     * Prepares view data for the dashboard, including:
     * - Datatables feature flag
     * - JavaScript asset list
     * - CSS asset list
     * - Page title
     * - Quick stats
     *
     * @return string Rendered admin dashboard view output.
     */
    public function index()
    {
        $model = new MessageModel();
        $db    = \Config\Database::connect();

        $data['stats'] = [
            'pending'       => $model->where('sent_at IS NULL')->countAllResults(),
            'sent_today'    => (int) $db->table('messages')
                                    ->where('DATE(sent_at)', date('Y-m-d'))
                                    ->where('deleted_at IS NULL')
                                    ->countAllResults(),
            'sent_month'    => (int) $db->table('messages')
                                    ->where('YEAR(sent_at)', date('Y'))
                                    ->where('MONTH(sent_at)', date('m'))
                                    ->where('deleted_at IS NULL')
                                    ->countAllResults(),
            'total'         => (int) $db->table('messages')
                                    ->where('deleted_at IS NULL')
                                    ->countAllResults(),
        ];

        // Datatables flag
        $data['datatables'] = true;
        // Array of javascript files to include
        $data['js'] = ['admin/home'];
        // Array of CSS files to include
        $data['css'] = ['admin/home'];
        // Set the page title
        $data['title'] = 'Mail Queue Management';
        return view('admin/home', $data);
    }
}
