<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Index
     *
     * Redirects to the admin panel.
     * Admin only.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        return redirect()->to('/admin');
    }
}
