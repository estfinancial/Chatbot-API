jQuery(document).ready(function($) {
    // Dashboard App
    if ($("#chatbot-dashboard-app").length) {
        function loadDashboardStats() {
            $.ajax({
                url: est_chatbot_admin_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "est_chatbot_get_stats",
                    nonce: est_chatbot_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        let html = `
                            <div class="dashboard-cards">
                                <div class="card">
                                    <h3>Total Conversations</h3>
                                    <p>${stats.totals.conversations}</p>
                                </div>
                                <div class="card">
                                    <h3>Total Leads</h3>
                                    <p>${stats.totals.leads}</p>
                                </div>
                                <div class="card">
                                    <h3>Total Messages</h3>
                                    <p>${stats.totals.messages}</p>
                                </div>
                            </div>
                            <div class="dashboard-section">
                                <h2>Recent Activity (Last 30 Days)</h2>
                                <div class="dashboard-cards">
                                    <div class="card">
                                        <h3>Conversations</h3>
                                        <p>${stats.recent.conversations_30d}</p>
                                    </div>
                                    <div class="card">
                                        <h3>Leads</h3>
                                        <p>${stats.recent.leads_30d}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="dashboard-section">
                                <h2>Lead Status Breakdown</h2>
                                <ul>
                        `;
                        for (const status in stats.lead_status) {
                            html += `<li>${status}: ${stats.lead_status[status]}</li>`;
                        }
                        html += `
                                </ul>
                            </div>
                            <div class="dashboard-section">
                                <h2>Service Popularity</h2>
                                <ul>
                        `;
                        for (const service in stats.service_popularity) {
                            html += `<li>${service}: ${stats.service_popularity[service]}</li>`;
                        }
                        html += `
                                </ul>
                            </div>
                        `;
                        $("#chatbot-dashboard-app").html(html);
                    } else {
                        $("#chatbot-dashboard-app").html(`<p>Error loading dashboard: ${response.data}</p>`);
                    }
                },
                error: function(xhr, status, error) {
                    $("#chatbot-dashboard-app").html(`<p>AJAX Error: ${error}</p>`);
                }
            });
        }
        loadDashboardStats();
    }

    // Conversations App
    if ($("#chatbot-conversations-app").length) {
        let currentPage = 1;
        const perPage = 20;

        function loadConversations(page) {
            $("#chatbot-conversations-app").html("<p>Loading conversations...</p>");
            $.ajax({
                url: est_chatbot_admin_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "est_chatbot_get_conversations",
                    nonce: est_chatbot_admin_ajax.nonce,
                    page: page,
                    per_page: perPage
                },
                success: function(response) {
                    if (response.success) {
                        const conversations = response.data.conversations;
                        const pagination = response.data.pagination;
                        let html = `
                            <table class="wp-list-table widefat fixed striped tags">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Session ID</th>
                                        <th>Started At</th>
                                        <th>Ended At</th>
                                        <th>Messages</th>
                                        <th>Status</th>
                                        <th>Has Lead</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        if (conversations.length > 0) {
                            conversations.forEach(conv => {
                                html += `
                                    <tr>
                                        <td>${conv.id}</td>
                                        <td>${conv.session_id.substring(0, 8)}...</td>
                                        <td>${new Date(conv.started_at).toLocaleString()}</td>
                                        <td>${conv.ended_at ? new Date(conv.ended_at).toLocaleString() : "N/A"}</td>
                                        <td>${conv.total_messages}</td>
                                        <td>${conv.status}</td>
                                        <td>${conv.has_lead ? "Yes" : "No"}</td>
                                        <td><button class="button view-conversation" data-id="${conv.id}">View</button></td>
                                    </tr>
                                `;
                            });
                        } else {
                            html += `<tr><td colspan="8">No conversations found.</td></tr>`;
                        }
                        html += `
                                </tbody>
                            </table>
                            <div class="tablenav bottom">
                                <div class="tablenav-pages">
                                    <span class="displaying-num">${pagination.total} items</span>
                                    <span class="pagination-links">
                        `;
                        if (pagination.has_prev) {
                            html += `<a class="prev-page button" href="#" data-page="${pagination.page - 1}">&laquo;</a>`;
                        }
                        html += `<span class="paging-input">${pagination.page} of <span class="total-pages">${pagination.pages}</span></span>`;
                        if (pagination.has_next) {
                            html += `<a class="next-page button" href="#" data-page="${pagination.page + 1}">&raquo;</a>`;
                        }
                        html += `
                                    </span>
                                </div>
                            </div>
                        `;
                        $("#chatbot-conversations-app").html(html);
                    } else {
                        $("#chatbot-conversations-app").html(`<p>Error loading conversations: ${response.data}</p>`);
                    }
                },
                error: function(xhr, status, error) {
                    $("#chatbot-conversations-app").html(`<p>AJAX Error: ${error}</p>`);
                }
            });
        }

        $(document).on("click", ".tablenav-pages a", function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data("page"));
            loadConversations(currentPage);
        });

        $(document).on("click", ".view-conversation", function() {
            const conversationId = $(this).data("id");
            $("#chatbot-conversations-app").html("<p>Loading messages...</p>");
            $.ajax({
                url: est_chatbot_admin_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "est_chatbot_get_conversation_messages",
                    nonce: est_chatbot_admin_ajax.nonce,
                    conversation_id: conversationId
                },
                success: function(response) {
                    if (response.success) {
                        const conv = response.data.conversation;
                        const messages = response.data.messages;
                        let html = `
                            <button class="button back-to-conversations">&laquo; Back to Conversations</button>
                            <h2>Conversation #${conv.id} (${conv.session_id.substring(0, 8)}...)</h2>
                            <p><strong>Started:</strong> ${new Date(conv.started_at).toLocaleString()}</p>
                            <p><strong>Ended:</strong> ${conv.ended_at ? new Date(conv.ended_at).toLocaleString() : "N/A"}</p>
                            <p><strong>Status:</strong> ${conv.status}</p>
                            <p><strong>Total Messages:</strong> ${conv.total_messages}</p>
                            <div class="conversation-messages">
                        `;
                        messages.forEach(msg => {
                            html += `
                                <div class="message ${msg.sender}">
                                    <strong>${msg.sender}:</strong> ${msg.content}
                                    <span class="timestamp">${new Date(msg.timestamp).toLocaleTimeString()}</span>
                                </div>
                            `;
                        });
                        html += `</div>`;
                        $("#chatbot-conversations-app").html(html);
                    } else {
                        $("#chatbot-conversations-app").html(`<p>Error loading messages: ${response.data}</p>`);
                    }
                },
                error: function(xhr, status, error) {
                    $("#chatbot-conversations-app").html(`<p>AJAX Error: ${error}</p>`);
                }
            });
        });

        $(document).on("click", ".back-to-conversations", function() {
            loadConversations(currentPage);
        });

        loadConversations(currentPage);
    }

    // Leads App
    if ($("#chatbot-leads-app").length) {
        let currentPage = 1;
        const perPage = 20;
        let currentStatusFilter = "all";

        function loadLeads(page, statusFilter) {
            $("#chatbot-leads-app").html("<p>Loading leads...</p>");
            $.ajax({
                url: est_chatbot_admin_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "est_chatbot_get_leads",
                    nonce: est_chatbot_admin_ajax.nonce,
                    page: page,
                    per_page: perPage,
                    status: statusFilter === "all" ? null : statusFilter
                },
                success: function(response) {
                    if (response.success) {
                        const leads = response.data.leads;
                        const pagination = response.data.pagination;
                        let html = `
                            <div class="lead-filters">
                                <label for="lead-status-filter">Filter by Status:</label>
                                <select id="lead-status-filter">
                                    <option value="all">All</option>
                                    <option value="new" ${currentStatusFilter === "new" ? "selected" : ""}>New</option>
                                    <option value="contacted" ${currentStatusFilter === "contacted" ? "selected" : ""}>Contacted</option>
                                    <option value="converted" ${currentStatusFilter === "converted" ? "selected" : ""}>Converted</option>
                                    <option value="lost" ${currentStatusFilter === "lost" ? "selected" : ""}>Lost</option>
                                </select>
                            </div>
                            <table class="wp-list-table widefat fixed striped tags">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Service</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th>Webhook Sent</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        if (leads.length > 0) {
                            leads.forEach(lead => {
                                html += `
                                    <tr>
                                        <td>${lead.id}</td>
                                        <td>${lead.name}</td>
                                        <td>${lead.email}</td>
                                        <td>${lead.phone}</td>
                                        <td>${lead.service_requested}</td>
                                        <td>${new Date(lead.created_at).toLocaleString()}</td>
                                        <td><span class="lead-status ${lead.status}">${lead.status}</span></td>
                                        <td>${lead.ghl_webhook_sent ? "Yes" : "No"}</td>
                                        <td><button class="button edit-lead" data-id="${lead.id}">Edit</button></td>
                                    </tr>
                                `;
                            });
                        } else {
                            html += `<tr><td colspan="9">No leads found.</td></tr>`;
                        }
                        html += `
                                </tbody>
                            </table>
                            <div class="tablenav bottom">
                                <div class="tablenav-pages">
                                    <span class="displaying-num">${pagination.total} items</span>
                                    <span class="pagination-links">
                        `;
                        if (pagination.has_prev) {
                            html += `<a class="prev-page button" href="#" data-page="${pagination.page - 1}">&laquo;</a>`;
                        }
                        html += `<span class="paging-input">${pagination.page} of <span class="total-pages">${pagination.pages}</span></span>`;
                        if (pagination.has_next) {
                            html += `<a class="next-page button" href="#" data-page="${pagination.page + 1}">&raquo;</a>`;
                        }
                        html += `
                                    </span>
                                </div>
                            </div>
                        `;
                        $("#chatbot-leads-app").html(html);
                    } else {
                        $("#chatbot-leads-app").html(`<p>Error loading leads: ${response.data}</p>`);
                    }
                },
                error: function(xhr, status, error) {
                    $("#chatbot-leads-app").html(`<p>AJAX Error: ${error}</p>`);
                }
            });
        }

        $(document).on("change", "#lead-status-filter", function() {
            currentStatusFilter = $(this).val();
            currentPage = 1;
            loadLeads(currentPage, currentStatusFilter);
        });

        $(document).on("click", ".tablenav-pages a", function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data("page"));
            loadLeads(currentPage, currentStatusFilter);
        });

        $(document).on("click", ".edit-lead", function() {
            const leadId = $(this).data("id");
            // For simplicity, we'll just show an alert to update status/notes
            // In a real app, this would open a modal or a new page
            const newStatus = prompt("Enter new status (new, contacted, converted, lost):");
            if (newStatus) {
                const notes = prompt("Enter notes (optional):");
                $.ajax({
                    url: est_chatbot_admin_ajax.ajax_url,
                    type: "POST",
                    data: {
                        action: "est_chatbot_update_lead_status",
                        nonce: est_chatbot_admin_ajax.nonce,
                        lead_id: leadId,
                        status: newStatus,
                        notes: notes
                    },
                    success: function(response) {
                        if (response.success) {
                            alert("Lead updated successfully!");
                            loadLeads(currentPage, currentStatusFilter);
                        } else {
                            alert(`Error updating lead: ${response.data}`);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert(`AJAX Error: ${error}`);
                    }
                });
            }
        });

        loadLeads(currentPage, currentStatusFilter);
    }
});

