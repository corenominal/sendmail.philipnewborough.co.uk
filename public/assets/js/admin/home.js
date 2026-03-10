document.addEventListener("DOMContentLoaded", function() {

    // Mark sidebar active link
    const sidebarLinks = document.querySelectorAll("#sidebar .nav-link");
    sidebarLinks.forEach(link => {
        if (link.getAttribute("href") === "/admin") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });

    // Active filter state
    let activeFilter = 'all';

    // Initialise DataTable
    const table = $('#messages-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/messages/datatable',
            data: function(d) {
                d.filter = activeFilter;
            },
        },
        columns: [
            { data: 'id',         title: '#',       orderable: true  },
            { data: 'from',       title: 'From',    orderable: true  },
            { data: 'to',         title: 'To',      orderable: true  },
            { data: 'subject',    title: 'Subject', orderable: true  },
            { data: 'mailtype',   title: 'Type',    orderable: true  },
            { data: 'created_at', title: 'Created', orderable: true  },
            { data: 'sent_at',    title: 'Sent',    orderable: true,
              render: function(data) { return data ? data : '<span class="badge bg-primary text-dark">Pending</span>'; } },
            { data: 'actions',    title: 'Actions', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { targets: 7, className: 'text-nowrap' },
        ],
        rowCallback: function(row, data) {
            if (data.sent_at) {
                $(row).addClass('sent');
            }
        },
        drawCallback: function() {
            document.querySelectorAll('#messages-datatable [data-bs-toggle="tooltip"]').forEach(function(el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
            updateDeletePendingBtn();
        },
    });

    // Toggle the Delete Pending button based on the current pending count
    function updateDeletePendingBtn() {
        const pending = parseInt(document.getElementById('stat-pending').textContent, 10);
        document.getElementById('btn-delete-pending').disabled = (pending === 0);
    }

    // Refresh stats cards
    function refreshStats() {
        fetch('/admin/messages/stats')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('stat-pending').textContent    = data.pending;
                document.getElementById('stat-sent-today').textContent = data.sent_today;
                document.getElementById('stat-sent-month').textContent = data.sent_month;
                document.getElementById('stat-total').textContent      = data.total;
                updateDeletePendingBtn();
            });
    }

    // Refresh button
    document.getElementById('btn-datatable-refresh').addEventListener('click', function() {
        table.ajax.reload(null, false);
        refreshStats();
    });

    // Filter dropdown
    document.querySelectorAll('[data-filter]').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            activeFilter = this.dataset.filter;
            document.getElementById('filter-label').textContent = this.textContent.trim();
            document.querySelectorAll('[data-filter]').forEach(function(el) {
                el.classList.toggle('active', el.dataset.filter === activeFilter);
            });
            table.ajax.reload(null, false);
        });
    });

    // Shared helpers
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // View modal
    const viewModalEl = document.getElementById('modal-view-message');
    const viewModal   = new bootstrap.Modal(viewModalEl);

    document.getElementById('messages-datatable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-view-message');
        if (!btn) return;

        const id = parseInt(btn.dataset.id, 10);
        fetch('/admin/messages/' + id)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                // Build details list
                const fields = [
                    ['From',    data.from],
                    ['To',      data.to],
                    data.cc  ? ['CC',  data.cc]  : null,
                    data.bcc ? ['BCC', data.bcc] : null,
                    ['Subject', data.subject],
                    ['Type',    data.mailtype],
                    ['Created', data.created_at],
                    data.sent_at ? ['Sent At', data.sent_at] : null,
                ];
                document.getElementById('view-message-details').innerHTML = fields
                    .filter(Boolean)
                    .map(function(pair) {
                        return '<dt class="col-sm-3">' + escapeHtml(pair[0]) + '</dt>'
                             + '<dd class="col-sm-9">' + escapeHtml(pair[1]) + '</dd>';
                    })
                    .join('');

                // Render body
                const bodyEl = document.getElementById('view-message-body');
                bodyEl.innerHTML = '';
                if (data.mailtype === 'html') {
                    const iframe = document.createElement('iframe');
                    iframe.setAttribute('sandbox', 'allow-same-origin');
                    iframe.style.cssText = 'width:100%;border:none;min-height:200px;';
                    bodyEl.appendChild(iframe);
                    iframe.addEventListener('load', function() {
                        try {
                            iframe.style.height = iframe.contentDocument.body.scrollHeight + 32 + 'px';
                        } catch (err) {
                            iframe.style.height = '400px';
                        }
                    });
                    iframe.srcdoc = data.body || '';
                } else {
                    const pre = document.createElement('pre');
                    pre.className = 'mb-0';
                    pre.style.whiteSpace = 'pre-wrap';
                    pre.textContent = data.body || '';
                    bodyEl.appendChild(pre);
                }

                viewModal.show();
            })
            .catch(function() {
                alert('Failed to load message.');
            });
    });

    // Resend modal
    const resendModalEl  = document.getElementById('modal-resend-message');
    const resendModal    = new bootstrap.Modal(resendModalEl);
    let   pendingResendId = null;

    resendModalEl.addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btn-confirm-resend');
        btn.disabled = false;
        btn.innerHTML = 'Resend';
    });

    document.getElementById('messages-datatable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-resend-message');
        if (!btn) return;
        pendingResendId = parseInt(btn.dataset.id, 10);
        resendModal.show();
    });

    document.getElementById('btn-confirm-resend').addEventListener('click', function() {
        if (!pendingResendId) return;
        const id = pendingResendId;
        pendingResendId = null;

        const btn = document.getElementById('btn-confirm-resend');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Resending&hellip;';

        fetch('/admin/messages/' + id + '/resend', { method: 'POST' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    resendModal.hide();
                    table.ajax.reload(null, false);
                    refreshStats();
                } else {
                    alert('Resend failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(function() {
                alert('Failed to resend message.');
            });
    });

    // Delete pending modal
    const deletePendingModalEl = document.getElementById('modal-delete-pending');
    const deletePendingModal   = new bootstrap.Modal(deletePendingModalEl);

    deletePendingModalEl.addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btn-confirm-delete-pending');
        btn.disabled = false;
        btn.innerHTML = 'Delete Pending';
    });

    document.getElementById('btn-delete-pending').addEventListener('click', function() {
        deletePendingModal.show();
    });

    document.getElementById('btn-confirm-delete-pending').addEventListener('click', function() {
        const btn = document.getElementById('btn-confirm-delete-pending');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting&hellip;';

        fetch('/admin/messages/pending', { method: 'DELETE' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    deletePendingModal.hide();
                    table.ajax.reload(null, false);
                    refreshStats();
                } else {
                    alert('Delete failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(function() {
                alert('Failed to delete pending messages.');
            });
    });

    // Delete modal
    const deleteModalEl  = document.getElementById('modal-delete-message');
    const deleteModal    = new bootstrap.Modal(deleteModalEl);
    let   pendingDeleteId = null;

    deleteModalEl.addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btn-confirm-delete');
        btn.disabled = false;
        btn.innerHTML = 'Delete';
    });

    document.getElementById('messages-datatable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-message');
        if (!btn) return;
        pendingDeleteId = parseInt(btn.dataset.id, 10);
        deleteModal.show();
    });

    document.getElementById('btn-confirm-delete').addEventListener('click', function() {
        if (!pendingDeleteId) return;
        const id = pendingDeleteId;
        pendingDeleteId = null;

        const btn = document.getElementById('btn-confirm-delete');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting&hellip;';

        fetch('/admin/messages/' + id, { method: 'DELETE' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    deleteModal.hide();
                    table.ajax.reload(null, false);
                    refreshStats();
                } else {
                    alert('Delete failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(function() {
                alert('Failed to delete message.');
            });
    });

});


