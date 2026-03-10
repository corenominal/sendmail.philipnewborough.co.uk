<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="border-bottom border-1 mb-4 pb-4 d-flex align-items-center justify-content-between gap-3">
                <h2 class="mb-0">Manage Mail Queue</h2>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="btn-datatable-filter" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel"></i><span class="d-none d-lg-inline"> <span id="filter-label">All Messages</span></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btn-datatable-filter">
                            <li><a class="dropdown-item active" href="#" data-filter="all">All Messages</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="pending">Pending</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="sent">Sent</a></li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-outline-primary" id="btn-datatable-refresh"><i class="bi bi-arrow-clockwise"></i><span class="d-none d-lg-inline"> Refresh</span></button>
                    <button type="button" class="btn btn-outline-danger" id="btn-delete-pending"><i class="bi bi-trash"></i><span class="d-none d-lg-inline"> Delete Pending</span></button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-2 fw-bold" id="stat-pending"><?= $stats['pending'] ?></div>
                            <div class="text-secondary small">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-2 fw-bold" id="stat-sent-today"><?= $stats['sent_today'] ?></div>
                            <div class="text-secondary small">Sent Today</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-2 fw-bold" id="stat-sent-month"><?= $stats['sent_month'] ?></div>
                            <div class="text-secondary small">Sent This Month</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-2 fw-bold" id="stat-total"><?= $stats['total'] ?></div>
                            <div class="text-secondary small">Total</div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="messages-datatable" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="modal-view-message" tabindex="-1" aria-labelledby="modalViewMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewMessageLabel">View Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0" id="view-message-details"></dl>
                <hr>
                <p class="fw-bold mb-2">Body</p>
                <div id="view-message-body" class="border rounded p-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Resend Confirm Modal -->
<div class="modal fade" id="modal-resend-message" tabindex="-1" aria-labelledby="modalResendMessageLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalResendMessageLabel">Confirm Resend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">A new copy of this message will be added to the queue. Do you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-resend">Resend</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Pending Confirm Modal -->
<div class="modal fade" id="modal-delete-pending" tabindex="-1" aria-labelledby="modalDeletePendingLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeletePendingLabel">Delete Pending Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete all pending messages from the queue? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-delete-pending">Delete Pending</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="modal-delete-message" tabindex="-1" aria-labelledby="modalDeleteMessageLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteMessageLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this message? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>