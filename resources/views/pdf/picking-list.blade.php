<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #111;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 8px;
        }

        .meta {
            margin-bottom: 16px;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 4px;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #f3f3f3;
            font-weight: bold;
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .package {
            margin-top: 4px;
            font-size: 10px;
            color: #444;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        <div>{{ $accountLabel }} : {{ $accountName }}</div>
        <div>{{ $generatedAtLabel }} : {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 4%;">#</th>
                <th style="width: 10%;">{{ $columns['main_sku'] }}</th>
                <th class="center" style="width: 8%;">{{ $columns['image'] }}</th>
                <th style="width: 28%;">{{ $columns['product_name'] }}</th>
                <th style="width: 12%;">{{ $columns['variant_sku'] }}</th>
                <th style="width: 16%;">{{ $columns['variant_name'] }}</th>
                <th class="center" style="width: 6%;">{{ $columns['quantity'] }}</th>
                <th style="width: 16%;">{{ $columns['order_sn'] }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="center">{{ $row->lineNumber }}</td>
                    <td>{{ $row->mainSku }}</td>
                    <td class="center"></td>
                    <td>{{ $row->productName }}</td>
                    <td>{{ $row->variantSku }}</td>
                    <td>{{ $row->variantName }}</td>
                    <td class="center">{{ $row->quantity }}</td>
                    <td>
                        {{ $row->orderSn }}
                        <div class="package">{{ $packageLabel }} 1</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
