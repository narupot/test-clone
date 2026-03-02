<!-- WMS Pickup API Management Page -->

@extends('layouts.app')

@section('title', 'WMS Pickup Management')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-truck"></i> WMS Pickup Management
            </h1>
            
            <!-- Notification Container -->
            <div id="notification-container"></div>
            
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="send-tab" data-bs-toggle="tab" data-bs-target="#send-panel" type="button" role="tab">
                        <i class="fas fa-paper-plane"></i> Send Truck Plan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-panel" type="button" role="tab">
                        <i class="fas fa-history"></i> View Logs
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content">
                
                <!-- Send Truck Plan Tab -->
                <div class="tab-pane fade show active" id="send-panel" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Send Truck Plan Data</h5>
                        </div>
                        <div class="card-body">
                            <form id="truck-plan-form">
                                <div class="form-group mb-3">
                                    <label for="pickuptime" class="form-label">Pickup Time *</label>
                                    <input 
                                        type="datetime-local" 
                                        id="pickuptime" 
                                        name="pickuptime" 
                                        class="form-control" 
                                        required
                                    >
                                    <small class="form-text text-muted">Format: YYYY-MM-DD HH:mm</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Truck Plan Orders *</label>
                                    <div id="orders-container">
                                        <div class="order-item row g-2 mb-2">
                                            <div class="col-md-8">
                                                <input 
                                                    type="text" 
                                                    name="order_id" 
                                                    class="form-control" 
                                                    placeholder="Main Order ID (e.g., SMM260108225455)"
                                                    required
                                                >
                                            </div>
                                            <div class="col-md-3">
                                                <input 
                                                    type="text" 
                                                    name="location" 
                                                    class="form-control" 
                                                    placeholder="Location (e.g., A0-1)"
                                                    required
                                                >
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm remove-order" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="add-order-btn" class="btn btn-secondary btn-sm mt-2">
                                        <i class="fas fa-plus"></i> Add Order
                                    </button>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check"></i> Send Truck Plan
                                    </button>
                                    <button type="reset" class="btn btn-secondary ms-2">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Response Display -->
                            <div id="response-container" class="mt-4" style="display: none;">
                                <h6>Response:</h6>
                                <pre id="response-output" class="bg-light p-3 rounded"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- View Logs Tab -->
                <div class="tab-pane fade" id="logs-panel" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Pickup Logs</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Filter Section -->
                            <div id="filter-container" class="mb-4"></div>
                            
                            <!-- Logs Table -->
                            <div id="logs-container">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/wms-api.js') }}"></script>
<script>
    // Handle add order button
    document.getElementById('add-order-btn').addEventListener('click', function() {
        const container = document.getElementById('orders-container');
        const newOrder = `
            <div class="order-item row g-2 mb-2">
                <div class="col-md-8">
                    <input 
                        type="text" 
                        name="order_id" 
                        class="form-control" 
                        placeholder="Main Order ID (e.g., SMM260108225455)"
                        required
                    >
                </div>
                <div class="col-md-3">
                    <input 
                        type="text" 
                        name="location" 
                        class="form-control" 
                        placeholder="Location (e.g., A0-1)"
                        required
                    >
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-order" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newOrder);
        
        // Add remove event to new button
        addRemoveEvent();
    });
    
    // Handle remove order button
    function addRemoveEvent() {
        document.querySelectorAll('.remove-order').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                this.closest('.order-item').remove();
            };
        });
    }
    
    // Initialize remove events
    addRemoveEvent();
    
    // Handle form submission
    document.getElementById('truck-plan-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const pickuptime = document.getElementById('pickuptime').value;
        const orderIds = document.querySelectorAll('input[name="order_id"]');
        const locations = document.querySelectorAll('input[name="location"]');
        
        const truckPlan = [];
        for (let i = 0; i < orderIds.length; i++) {
            if (orderIds[i].value && locations[i].value) {
                truckPlan.push({
                    MainOrder: orderIds[i].value,
                    Location: locations[i].value
                });
            }
        }
        
        if (!pickuptime || truckPlan.length === 0) {
            showNotification('error', 'Please fill in all required fields');
            return;
        }
        
        const pickupData = {
            Pickuptime: new Date(pickuptime).toISOString().slice(0, 16),
            TruckPlan: truckPlan
        };
        
        try {
            const response = await fetch('/api/wms/truck-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(pickupData)
            });
            
            const data = await response.json();
            
            // Display response
            const responseContainer = document.getElementById('response-container');
            const responseOutput = document.getElementById('response-output');
            responseOutput.textContent = JSON.stringify(data, null, 2);
            responseContainer.style.display = 'block';
            
            if (response.ok && data.status === 'success') {
                showNotification('success', `Successfully processed. ${data.summary.updated} orders updated.`);
                if (data.summary.failed > 0) {
                    showNotification('warning', `${data.summary.failed} orders failed to update.`);
                }
            } else {
                showNotification('error', data.message || 'Error processing truck plan');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Request failed: ' + error.message);
        }
    });
    
    // Refresh logs when switching to logs tab
    document.getElementById('logs-tab').addEventListener('click', function() {
        displayLogsTable({ per_page: 20 });
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-color: #0d6efd;
    }
    
    .order-item {
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid #0d6efd;
    }
    
    .filter-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }
    
    #response-output {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
    }
    
    .spinner-border {
        color: #0d6efd;
    }
</style>
@endpush
