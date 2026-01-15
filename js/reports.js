$(document).ready(function() {
    let currentReportData = null;
    let currentReportType = null;

    // Generate Report
    $('#generateReport').on('click', function() {
        generateReport();
    });

    // Export PDF
    $('#exportPdfBtn').on('click', function() {
        if (!currentReportData) {
            alert('Please generate a report first');
            return;
        }
        exportToPDF();
    });

    // Export CSV
    $('#exportExcelBtn').on('click', function() {
        if (!currentReportData) {
            alert('Please generate a report first');
            return;
        }
        exportToCSV();
    });

    function generateReport() {
        const reportType = $('#reportType').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        currentReportType = reportType;

        $.ajax({
            url: '../../controller/backend_reports.php',
            method: 'POST',
            data: {
                action: reportType,
                startDate: startDate,
                endDate: endDate
            },
            dataType: 'json',
            beforeSend: function() {
                $('#reportContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Generating report...</p></div>');
            },
            success: function(response) {
                if (response.success) {
                    currentReportData = response.data;
                    
                    // Update summary cards
                    if (response.summary) {
                        updateSummaryCards(response.summary);
                    }
                    
                    // Update date range
                    $('#reportDateRange').text(formatDate(startDate) + ' to ' + formatDate(endDate));
                    
                    // Render report based on type
                    renderReport(reportType, response.data);
                } else {
                    $('#reportContent').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('#reportContent').html('<div class="alert alert-danger">Failed to generate report</div>');
            }
        });
    }

    function updateSummaryCards(summary) {
        $('#totalRevenue').text('₱' + parseFloat(summary.total_revenue).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalProfit').text('₱' + parseFloat(summary.total_profit).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        $('#totalTransactions').text(summary.total_transactions.toLocaleString());
        $('#profitMargin').text(summary.profit_margin.toFixed(2) + '%');
    }

    function renderReport(type, data) {
        let html = '';
        let title = '';
        let icon = '';
        let color = '';

        switch(type) {
            case 'sales_summary':
                title = 'Sales Summary';
                icon = 'solar:chart-bold';
                color = '#3b82f6';
                html = renderSalesSummary(data);
                break;
            case 'inventory':
                title = 'Inventory Status';
                icon = 'solar:box-bold';
                color = '#8b5cf6';
                html = renderInventory(data);
                break;
            case 'profit_loss':
                title = 'Profit & Loss';
                icon = 'solar:wallet-money-bold';
                color = '#10b981';
                html = renderProfitLoss(data);
                break;
            case 'product_performance':
                title = 'Product Performance';
                icon = 'solar:star-bold';
                color = '#f59e0b';
                html = renderProductPerformance(data);
                break;
            case 'transaction':
                title = 'Transaction Report';
                icon = 'solar:bill-check-bold';
                color = '#06b6d4';
                html = renderTransactions(data);
                break;
            case 'customer_sales':
                title = 'Customer Sales';
                icon = 'solar:users-group-rounded-bold';
                color = '#ec4899';
                html = renderCustomerSales(data);
                break;
            case 'low_stock':
                title = 'Low Stock Alert';
                icon = 'solar:danger-triangle-bold';
                color = '#ef4444';
                html = renderLowStock(data);
                break;
        }

        $('#reportTitle').html(`
            <span class="iconify me-2" data-icon="${icon}" style="color: ${color};"></span>
            <span class="fw-bolder" style="color: ${color};">${title.split(' ')[0]}</span> ${title.split(' ').slice(1).join(' ')}
        `);
        $('#reportContent').html(html);
    }

    function renderSalesSummary(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-info mx-3 my-4"><span class="iconify me-2" data-icon="solar:info-circle-bold"></span>No data available for this period</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>Date</th><th>Transactions</th><th>Items Sold</th><th>Revenue</th><th>Cost</th><th>Profit</th><th>Margin %</th>';
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            const revenue = parseFloat(row.revenue || 0);
            const cost = parseFloat(row.cost || 0);
            const profit = parseFloat(row.profit || 0);
            const margin = revenue > 0 ? (profit / revenue * 100).toFixed(2) : 0;

            html += '<tr>';
            html += `<td>${formatDate(row.date)}</td>`;
            html += `<td>${row.transactions}</td>`;
            html += `<td>${row.items_sold}</td>`;
            html += `<td>₱${revenue.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${cost.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${profit.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>${margin}%</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderInventory(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-info mx-3 my-4"><span class="iconify me-2" data-icon="solar:info-circle-bold"></span>No inventory data available</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>ID</th><th>Product Name</th><th>Category</th><th>Stock</th><th>Unit</th><th>Cost</th><th>Price</th><th>Stock Value</th><th>Status</th>';
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            const statusClass = row.stock_status === 'Critical' ? 'danger' : (row.stock_status === 'Low' ? 'warning' : 'success');
            
            html += '<tr>';
            html += `<td>${row.id}</td>`;
            html += `<td>${row.name}</td>`;
            html += `<td>${row.category}</td>`;
            html += `<td>${row.stock}</td>`;
            html += `<td>${row.unit}</td>`;
            html += `<td>₱${parseFloat(row.cost).toFixed(2)}</td>`;
            html += `<td>₱${parseFloat(row.price).toFixed(2)}</td>`;
            html += `<td>₱${parseFloat(row.stock_value).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td><span class="badge bg-${statusClass}">${row.stock_status}</span></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderProfitLoss(data) {
        let html = '<div class="p-3"><div class="row g-3">';
        html += '<div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">';
        html += '<h6 class="fw-bold mb-3 text-primary"><span class="iconify me-2" data-icon="solar:wallet-money-bold"></span>Financial Summary</h6>';
        html += '<table class="table table-sm mb-0">';
        html += `<tr><td class="fw-semibold text-muted">Total Revenue:</td><td class="text-end fw-bold">₱${parseFloat(data.total_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
        html += `<tr><td class="fw-semibold text-muted">Total Cost:</td><td class="text-end fw-bold">₱${parseFloat(data.total_cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
        html += `<tr class="table-success"><td class="fw-bold">Gross Profit:</td><td class="text-end fw-bold">₱${parseFloat(data.gross_profit || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
        html += `<tr><td class="fw-semibold text-muted">Profit Margin:</td><td class="text-end fw-bold text-success">${parseFloat(data.profit_margin || 0).toFixed(2)}%</td></tr>`;
        html += '</table></div></div></div>';
        
        html += '<div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">';
        html += '<h6 class="fw-bold mb-3 text-info"><span class="iconify me-2" data-icon="solar:bill-check-bold"></span>Transaction Summary</h6>';
        html += '<table class="table table-sm mb-0">';
        html += `<tr><td class="fw-semibold text-muted">Total Transactions:</td><td class="text-end fw-bold">${data.total_transactions}</td></tr>`;
        html += `<tr><td class="fw-semibold text-muted">Total Items Sold:</td><td class="text-end fw-bold">${data.total_items_sold}</td></tr>`;
        html += `<tr><td class="fw-semibold text-muted">Avg Transaction Value:</td><td class="text-end fw-bold">₱${parseFloat(data.avg_transaction_value || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
        html += '</table></div></div></div>';
        html += '</div></div>';
        
        return html;
    }

    function renderProductPerformance(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-info mx-3 my-4"><span class="iconify me-2" data-icon="solar:info-circle-bold"></span>No product data available for this period</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>Rank</th><th>Product</th><th>Category</th><th>Qty Sold</th><th>Revenue</th><th>Profit</th><th>Transactions</th><th>Current Stock</th>';
        html += '</tr></thead><tbody>';

        data.forEach((row, index) => {
            html += '<tr>';
            html += `<td>${index + 1}</td>`;
            html += `<td>${row.name}</td>`;
            html += `<td>${row.category}</td>`;
            html += `<td>${row.qty_sold}</td>`;
            html += `<td>₱${parseFloat(row.revenue).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${parseFloat(row.profit).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>${row.transactions}</td>`;
            html += `<td>${row.current_stock}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderTransactions(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-info mx-3 my-4"><span class="iconify me-2" data-icon="solar:info-circle-bold"></span>No transactions found for this period</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>Receipt #</th><th>Date</th><th>Customer</th><th>Items</th><th>Qty</th><th>Total</th><th>Cost</th><th>Profit</th>';
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            html += '<tr>';
            html += `<td>${row.receipt_id}</td>`;
            html += `<td>${formatDateTime(row.charge_date)}</td>`;
            html += `<td>${row.customer_name || 'Sample Name'}</td>`;
            
            // Please querry the name from customer name in table "Customers" to get customer name
            html += `<td>${row.items_count}</td>`;
            html += `<td>${row.total_qty}</td>`;
            html += `<td>₱${parseFloat(row.total_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${parseFloat(row.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${parseFloat(row.profit || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderCustomerSales(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-info mx-3 my-4"><span class="iconify me-2" data-icon="solar:info-circle-bold"></span>No customer data available for this period</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover table-striped" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>Rank</th><th>Customer Name</th><th>Purchases</th><th>Total Spent</th><th>Avg Purchase</th><th>Items Bought</th><th>Last Purchase</th>';
        html += '</tr></thead><tbody>';

        data.forEach((row, index) => {
            html += '<tr>';
            html += `<td>${index + 1}</td>`;
            html += `<td>${row.customer_name}</td>`;
            html += `<td>${row.total_purchases}</td>`;
            html += `<td>₱${parseFloat(row.total_spent).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>₱${parseFloat(row.avg_purchase).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td>${row.total_items}</td>`;
            html += `<td>${formatDate(row.last_purchase)}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderLowStock(data) {
        if (!data || data.length === 0) {
            return '<div class="alert alert-success mx-3 my-4"><span class="iconify me-2" data-icon="solar:check-circle-bold"></span>All items are well stocked!</div>';
        }

        let html = '<div class="table-responsive"><table class="table table-hover" id="reportTable">';
        html += '<thead><tr>';
        html += '<th>Alert</th><th>ID</th><th>Product</th><th>Category</th><th>Stock</th><th>Unit</th><th>Sold (30d)</th><th>Stock Value</th><th>Action Needed</th>';
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            const alertClass = row.alert_level === 'Critical' ? 'danger' : 'warning';
            const actionNeeded = row.alert_level === 'Critical' ? 'URGENT: Reorder Now' : 'Reorder Soon';
            
            html += '<tr>';
            html += `<td><span class="badge bg-${alertClass}">${row.alert_level}</span></td>`;
            html += `<td>${row.id}</td>`;
            html += `<td>${row.name}</td>`;
            html += `<td>${row.category}</td>`;
            html += `<td class="fw-bold">${row.stock}</td>`;
            html += `<td>${row.unit}</td>`;
            html += `<td>${row.sold_last_30_days}</td>`;
            html += `<td>₱${parseFloat(row.stock_value).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>`;
            html += `<td><span class="text-${alertClass} fw-bold">${actionNeeded}</span></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4');
        
        // Get clean text without HTML/icon elements
        const reportTitleElement = document.querySelector('#reportTitle');
        const reportTitle = reportTitleElement ? reportTitleElement.innerText.replace(/\s+/g, ' ').trim() : 'Report';
        
        const dateRangeElement = document.querySelector('#reportDateRange');
        const dateRange = dateRangeElement ? dateRangeElement.innerText.replace(/\s+/g, ' ').trim() : '';
        
        // Add title
        doc.setFontSize(18);
        doc.text('GOP Marketing - ' + reportTitle, 14, 15);
        
        doc.setFontSize(10);
        if (dateRange) {
            doc.text(dateRange, 14, 22);
        }
        
        // Get summary values and clean them
        const revenueEl = document.querySelector('#totalRevenue');
        const profitEl = document.querySelector('#totalProfit');
        const transactionsEl = document.querySelector('#totalTransactions');
        const marginEl = document.querySelector('#profitMargin');
        
        const revenue = revenueEl ? revenueEl.innerText.replace(/\s+/g, '').trim() : 'P0.00';
        const profit = profitEl ? profitEl.innerText.replace(/\s+/g, '').trim() : 'P0.00';
        const transactions = transactionsEl ? transactionsEl.innerText.replace(/\s+/g, '').trim() : '0';
        const margin = marginEl ? marginEl.innerText.replace(/\s+/g, '').trim() : '0%';
        
        // Replace ₱ with P for better PDF rendering
        const cleanRevenue = revenue.replace('₱', 'P');
        const cleanProfit = profit.replace('₱', 'P');
        
        // Add summary
        doc.setFontSize(12);
        doc.text('Summary:', 14, 30);
        doc.setFontSize(10);
        doc.text('Revenue: ' + cleanRevenue + '  |  Profit: ' + cleanProfit + '  |  Transactions: ' + transactions + '  |  Margin: ' + margin, 14, 36);
        
        // Add table
        const table = document.querySelector('#reportTable');
        if (table) {
            doc.autoTable({
                html: table,
                startY: 42,
                theme: 'grid',
                headStyles: { fillColor: [59, 130, 246] },
                styles: { fontSize: 8 },
                didParseCell: function(data) {
                    // Replace ₱ with P in all cells for better rendering
                    if (data.cell.text && data.cell.text.length > 0) {
                        data.cell.text = data.cell.text.map(text => 
                            typeof text === 'string' ? text.replace(/₱/g, 'P') : text
                        );
                    }
                }
            });
        }
        
        // Add footer
        const pageCount = doc.internal.getNumberOfPages();
        for(let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text('Page ' + i + ' of ' + pageCount, doc.internal.pageSize.getWidth() / 2, doc.internal.pageSize.getHeight() - 10, {align: 'center'});
            doc.text('Generated on: ' + new Date().toLocaleString(), 14, doc.internal.pageSize.getHeight() - 10);
        }
        
        doc.save(currentReportType + '_report_' + new Date().toISOString().slice(0,10) + '.pdf');
    }

    function exportToCSV() {
        const table = document.querySelector('#reportTable');
        if (!table) {
            alert('No table data to export');
            return;
        }

        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText;
                // Remove special characters and clean up
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            
            csv.push(row.join(','));
        }
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `${currentReportType}_report_${new Date().toISOString().slice(0,10)}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    // Auto-generate initial report
    generateReport();
});
