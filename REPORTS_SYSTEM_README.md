# GOP Marketing POS - Reports System

## Overview
Complete reporting system with 7 different report types, date range filtering, and export functionality (PDF & CSV).

## Files Created
1. **views/reports/reports.php** - Main reports page with UI
2. **controller/backend_reports.php** - Backend controller with database queries
3. **js/reports.js** - Frontend JavaScript for report generation and export
4. **styles/reports.css** - Styling for reports page

## Features

### 7 Report Types

1. **Sales Summary Report**
   - Daily breakdown of sales
   - Columns: Date, Transactions, Items Sold, Revenue, Cost, Profit, Margin %
   - Shows transaction performance day by day

2. **Inventory Status Report**
   - Current stock levels for all products
   - Columns: ID, Product Name, Category, Stock, Unit, Cost, Price, Stock Value, Status
   - Status indicators: Critical (red), Low (yellow), Good (green)

3. **Profit & Loss Report**
   - Overall financial summary
   - Shows: Total Revenue, Total Cost, Gross Profit, Profit Margin
   - Transaction metrics: Total Transactions, Items Sold, Average Transaction Value

4. **Product Performance Report**
   - Top 50 products by quantity sold
   - Columns: Rank, Product, Category, Qty Sold, Revenue, Profit, Transactions, Current Stock
   - Ranked from highest to lowest sales

5. **Transaction Report**
   - Last 500 transactions with details
   - Columns: Receipt #, Date, Customer, Items, Qty, Total, Cost, Profit
   - Full transaction history with profit calculation

6. **Customer Sales Report**
   - Top 100 customers by total spending
   - Columns: Rank, Customer Name, Purchases, Total Spent, Avg Purchase, Items Bought, Last Purchase
   - Identifies most valuable customers

7. **Low Stock Alert Report**
   - Items with stock below 15 units
   - Columns: Alert Level, ID, Product, Category, Stock, Unit, Sold (30d), Stock Value, Action Needed
   - Critical alerts for items with stock < 5
   - Shows 30-day sales velocity for reorder planning

### Date Range Filtering
- Start Date and End Date selectors
- Applies to all report types
- Default: Current month

### Summary Cards
- Total Revenue (₱)
- Total Profit (₱)
- Total Transactions
- Profit Margin (%)
- Updates dynamically with each report

### Export Functionality

**PDF Export:**
- Landscape A4 format
- Includes report title and date range
- Summary statistics in header
- Full table data with styling
- Page numbers and generation timestamp
- Filename: `{report_type}_report_{date}.pdf`

**CSV Export:**
- Full table data export
- Properly formatted with quotes
- Compatible with Excel/Google Sheets
- Filename: `{report_type}_report_{date}.csv`

## Database Schema Used

### Tables:
- **charges** - Main transaction table
- **charge_items** - Transaction line items
- **items** - Product catalog
- **customers** - Customer information

### Key Calculations:
- **Revenue** = SUM(quantity × price)
- **Cost** = SUM(quantity × cost)
- **Profit** = Revenue - Cost
- **Profit Margin** = (Profit / Revenue) × 100
- **Stock Value** = stock × cost

## Usage

1. **Access the Reports Page:**
   - Click "Reports" in the sidebar menu
   - Located at: `views/reports/reports.php`

2. **Generate a Report:**
   - Select report type from dropdown
   - Choose date range (start and end dates)
   - Click "Generate Report" button

3. **Export Report:**
   - Click "Export PDF" for PDF download
   - Click "Export CSV" for Excel-compatible CSV

4. **View Summary:**
   - Summary cards update automatically with each report
   - Shows key financial metrics

## Technical Details

### Backend (backend_reports.php)
- Uses MySQLi prepared statements
- Returns JSON responses
- Includes error handling
- Methods:
  - `getSalesSummary()` - Daily sales breakdown
  - `getInventoryStatus()` - Stock levels
  - `getProfitLoss()` - Financial summary
  - `getProductPerformance()` - Top products
  - `getTransactions()` - Transaction list
  - `getCustomerSales()` - Customer rankings
  - `getLowStockAlert()` - Low stock items
  - `getSummaryStats()` - Dashboard metrics

### Frontend (reports.js)
- jQuery-based AJAX calls
- Dynamic table rendering for each report type
- jsPDF and jsPDF-autotable for PDF export
- CSV generation from HTML tables
- Date formatting functions
- Loading states and error handling

### Styling (reports.css)
- Modern card-based layout
- Responsive design (mobile-friendly)
- Color-coded status indicators
- Print-optimized styles
- Hover effects and transitions
- Professional table styling

## Status Indicators

### Inventory Status:
- **Critical** (Red): Stock < 5
- **Low** (Yellow): Stock 5-14
- **Good** (Green): Stock ≥ 15

### Low Stock Alert:
- **Critical**: Immediate reorder needed (stock < 5)
- **Warning**: Reorder soon (stock 5-14)

## Auto-Load Behavior
- Reports page automatically generates "Sales Summary" report for current month on load
- Users can change report type and date range as needed

## Dependencies
- jsPDF 2.5.1 (included in reports.php)
- jsPDF-autotable 3.5.31 (included in reports.php)
- jQuery 3.7.1 (already in project)
- Bootstrap 5.3.6 (already in project)
- Chart.js (not used in reports, but available if needed)

## Future Enhancements (Optional)
- Schedule automated report emails
- Add charts/graphs for visual data representation
- Custom report builder
- Report templates
- Multi-location support
- Comparison between periods
- Forecasting based on historical data

## Notes
- All monetary values are in Philippine Peso (₱)
- Dates are formatted in locale-appropriate format
- Reports handle empty data gracefully
- All SQL queries use prepared statements for security
- Export functions check if data exists before exporting
