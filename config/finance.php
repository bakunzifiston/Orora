<?php

return [

    'expense_category_to_account' => [
        'feed.purchase' => '5000',
        'feed.transport' => '5300',
        'feed.storage' => '5000',
        'health.vaccination' => '5100',
        'health.treatment' => '5100',
        'health.vet_visit' => '5100',
        'health.diagnostics' => '5100',
        'farm.labor' => '5200',
        'farm.utilities' => '5900',
        'farm.equipment' => '5400',
        'farm.maintenance' => '5400',
        'farm.structures' => '5400',
        'general.admin' => '5900',
        'general.transport' => '5300',
        'general.insurance' => '5900',
        'general.other' => '5900',
    ],

    'default_expense_account' => '5900',

    'sale_type_to_revenue_account' => [
        'milk_sale' => '4000',
        'animal_sale' => '4100',
        'meat_sale' => '4200',
    ],

    'cash_accounts' => [
        'cash' => '1000',
        'bank' => '1100',
    ],

    'bank_payment_methods' => [
        'bank_transfer',
        'mobile_money',
        'cheque',
        'card',
    ],

    'accounts_receivable' => '1200',
    'taxes_payable' => '2100',

    'finance_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'finance.overview'],
        ['key' => 'transactions', 'label' => 'Transactions', 'route' => 'finance.transactions'],
        ['key' => 'profit_loss', 'label' => 'P&L', 'route' => 'finance.reports.profit_loss'],
        ['key' => 'cash_flow', 'label' => 'Cash flow', 'route' => 'finance.reports.cash_flow'],
    ],

];