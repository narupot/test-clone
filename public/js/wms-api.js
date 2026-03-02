/**
 * WMS Pickup API - JavaScript Examples
 * 
 * Usage examples for WMS Pickup API endpoints
 */

// Example 1: Update Truck Plan
// =============================

const updateTruckPlan = async () => {
    const pickupData = {
        "Pickuptime": "2025-10-20 04:00",
        "TruckPlan": [
            { "MainOrder": "SMM260108225455", "Location": "A0-1" },
            { "MainOrder": "SMM260108225456", "Location": "A1-1" },
            { "MainOrder": "SMM260108225457", "Location": "A2-1" },
            { "MainOrder": "SMM260108225458", "Location": "A3-1" }
        ]
    };

    try {
        const response = await fetch('/api/wms/truck-plan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(pickupData)
        });

        const data = await response.json();
        
        if (response.ok) {
            console.log('Success:', data);
            showNotification('success', `${data.summary.updated} orders updated successfully`);
            
            if (data.summary.failed > 0) {
                showNotification('warning', `${data.summary.failed} orders failed`);
                console.log('Failed orders:', data.failed_orders);
            }
        } else {
            console.error('Error:', data);
            showNotification('error', data.message || 'Error processing truck plan');
        }
        
        return data;
    } catch (error) {
        console.error('Request failed:', error);
        showNotification('error', 'Request failed: ' + error.message);
        throw error;
    }
};

// Example 2: Get All Pickup Logs
// ==============================

const getPickupLogs = async (filters = {}) => {
    try {
        const queryParams = new URLSearchParams();
        
        if (filters.status) queryParams.append('status', filters.status);
        if (filters.from_date) queryParams.append('from_date', filters.from_date);
        if (filters.to_date) queryParams.append('to_date', filters.to_date);
        if (filters.per_page) queryParams.append('per_page', filters.per_page);
        
        const response = await fetch(`/api/wms/logs?${queryParams.toString()}`);
        const data = await response.json();
        
        console.log('Logs:', data);
        return data;
    } catch (error) {
        console.error('Failed to fetch logs:', error);
        throw error;
    }
};

// Example 3: Get Log Detail
// ==========================

const getLogDetail = async (logId) => {
    try {
        const response = await fetch(`/api/wms/logs/${logId}`);
        
        if (!response.ok) {
            throw new Error('Log not found');
        }
        
        const data = await response.json();
        console.log('Log detail:', data);
        return data;
    } catch (error) {
        console.error('Failed to fetch log detail:', error);
        throw error;
    }
};

// Example 4: Display Logs in Table
// =================================

const displayLogsTable = async (filters = {}) => {
    try {
        const data = await getPickupLogs(filters);
        
        if (!data.data || !data.data.data || data.data.data.length === 0) {
            console.log('No logs found');
            return;
        }
        
        const logs = data.data.data;
        let html = `
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pickup Time</th>
                        <th>Total Orders</th>
                        <th>Updated</th>
                        <th>Failed</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        logs.forEach(log => {
            const statusBadge = `<span class="badge badge-${log.status === 'success' ? 'success' : (log.status === 'partial' ? 'warning' : 'danger')}">${log.status}</span>`;
            
            html += `
                <tr>
                    <td>${log.id}</td>
                    <td>${log.pickup_time}</td>
                    <td>${log.total_orders}</td>
                    <td><span class="badge badge-success">${log.updated_count}</span></td>
                    <td><span class="badge badge-danger">${log.failed_count}</span></td>
                    <td>${statusBadge}</td>
                    <td>${new Date(log.created_at).toLocaleString()}</td>
                    <td>
                        <button onclick="viewLogDetail(${log.id})" class="btn btn-sm btn-info">View</button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
        `;
        
        // Insert into DOM (assumes there's an element with id 'logs-container')
        const container = document.getElementById('logs-container');
        if (container) {
            container.innerHTML = html;
        }
        
    } catch (error) {
        console.error('Failed to display logs:', error);
    }
};

// Example 5: View Log Detail in Modal
// ====================================

const viewLogDetail = async (logId) => {
    try {
        const response = await fetch(`/api/wms/logs/${logId}`);
        const data = await response.json();
        
        if (data.status !== 'success') {
            showNotification('error', data.message);
            return;
        }
        
        const log = data.data;
        
        const modalContent = `
            <div class="modal-header">
                <h5 class="modal-title">Log Detail #${log.id}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">Pickup Time:</dt>
                    <dd class="col-sm-9">${log.pickup_time}</dd>
                    
                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9">
                        <span class="badge badge-${log.status === 'success' ? 'success' : (log.status === 'partial' ? 'warning' : 'danger')}">
                            ${log.status}
                        </span>
                    </dd>
                    
                    <dt class="col-sm-3">Total Orders:</dt>
                    <dd class="col-sm-9">${log.total_orders}</dd>
                    
                    <dt class="col-sm-3">Updated:</dt>
                    <dd class="col-sm-9">${log.updated_count}</dd>
                    
                    <dt class="col-sm-3">Failed:</dt>
                    <dd class="col-sm-9">${log.failed_count}</dd>
                    
                    <dt class="col-sm-3">Created At:</dt>
                    <dd class="col-sm-9">${new Date(log.created_at).toLocaleString()}</dd>
                </dl>
                
                <h6>Truck Plan:</h6>
                <pre>${JSON.stringify(log.truck_plan, null, 2)}</pre>
                
                ${log.failed_orders && log.failed_orders.length > 0 ? `
                    <h6>Failed Orders:</h6>
                    <pre>${JSON.stringify(log.failed_orders, null, 2)}</pre>
                ` : ''}
            </div>
        `;
        
        // Assuming Bootstrap modal exists
        const modal = document.getElementById('detailModal');
        if (modal) {
            modal.querySelector('.modal-content').innerHTML = modalContent;
            new bootstrap.Modal(modal).show();
        }
        
    } catch (error) {
        console.error('Failed to fetch log detail:', error);
        showNotification('error', 'Failed to fetch log detail');
    }
};

// Example 6: Form Submission
// ===========================

const handleTruckPlanForm = async (event) => {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const pickuptime = formData.get('pickuptime');
    const orderData = [];
    
    // Parse multiple order inputs
    const orderCount = formData.getAll('order_id').length;
    for (let i = 0; i < orderCount; i++) {
        const orderId = formData.getAll('order_id')[i];
        const location = formData.getAll('location')[i];
        
        if (orderId && location) {
            orderData.push({
                MainOrder: orderId,
                Location: location
            });
        }
    }
    
    if (!pickuptime || orderData.length === 0) {
        showNotification('error', 'Please fill in all required fields');
        return;
    }
    
    const requestData = {
        Pickuptime: pickuptime,
        TruckPlan: orderData
    };
    
    try {
        const response = await updateTruckPlan();
        // Form submission is handled by updateTruckPlan function
    } catch (error) {
        console.error('Form submission failed:', error);
    }
};

// Example 7: Show Filter UI
// ==========================

const showFilterUI = () => {
    const html = `
        <div class="filter-section">
            <h5>Filter Logs</h5>
            <form id="filter-form">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="partial">Partial</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="from_date">From Date:</label>
                    <input type="datetime-local" id="from_date" name="from_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="to_date">To Date:</label>
                    <input type="datetime-local" id="to_date" name="to_date" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </form>
        </div>
    `;
    
    const container = document.getElementById('filter-container');
    if (container) {
        container.innerHTML = html;
        
        document.getElementById('filter-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const filters = {
                status: document.getElementById('status').value,
                from_date: document.getElementById('from_date').value,
                to_date: document.getElementById('to_date').value,
                per_page: 20
            };
            
            await displayLogsTable(filters);
        });
    }
};

// Helper: Show Notification
// ==========================

const showNotification = (type, message) => {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.getElementById('notification-container');
    if (container) {
        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHtml;
        container.appendChild(alertElement);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertElement.remove();
        }, 5000);
    }
};

// Initialize on page load
// =======================

document.addEventListener('DOMContentLoaded', () => {
    // Show filter UI
    showFilterUI();
    
    // Load initial logs
    displayLogsTable({ per_page: 20 });
    
    // Handle truck plan form submission
    const truckPlanForm = document.getElementById('truck-plan-form');
    if (truckPlanForm) {
        truckPlanForm.addEventListener('submit', handleTruckPlanForm);
    }
});

// Export functions for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updateTruckPlan,
        getPickupLogs,
        getLogDetail,
        displayLogsTable,
        viewLogDetail,
        showFilterUI,
        showNotification
    };
}
