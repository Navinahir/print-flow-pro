<?php

declare(strict_types=1);

return [

    'brand' => [
        'name' => 'XY Cubic Shopee',
        'tagline' => '專為 Shopee 賣家打造的列印工作流程',
    ],

    'errors' => [
        'region_inactive' => '此區域尚未啟用。',
    ],

    'general' => [
        'not_available' => '—',
    ],

    'nav' => [
        'dashboard' => '儀表板',
        'operations' => '作業',
        'uploads' => '上傳',
        'upload_history' => '上傳紀錄',
        'new_upload' => '新增上傳',
        'printing' => '列印',
        'printing_modules' => '列印模組',
        'order_details' => '訂單明細',
        'logistics_labels' => '物流標籤',
        'picking_list' => '揀貨單',
        'delivery_labels' => '配送標籤',
        'coming_soon' => '即將推出',
        'account' => '帳戶',
        'profile' => '個人資料',
        'logout' => '登出',
        'open_menu' => '開啟導覽選單',
        'close_menu' => '關閉導覽選單',
        'toggle_sidebar' => '切換側邊欄',
        'collapse_sidebar' => '收合側邊欄',
        'expand_sidebar' => '展開側邊欄',
    ],

    'sidebar' => [
        'footer_label' => '帳戶操作',
    ],

    'form' => [
        'required_indicator' => '必填',
    ],

    'header' => [
        'welcome' => '歡迎，:name',
        'merchant_label' => '商家',
    ],

    'locale' => [
        'switcher_label' => '語言',
        'validation' => [
            'required' => '請選擇語言。',
            'unsupported' => '此區域不支援所選語言。',
        ],
    ],

    'theme' => [
        'switcher_label' => '主題',
        'light' => '淺色模式',
        'dark' => '深色模式',
        'system' => '跟隨系統',
        'updated' => '主題偏好已儲存。',
        'validation' => [
            'required' => '請選擇主題。',
            'unsupported' => '不支援此主題選項。',
        ],
    ],

    'user_menu' => [
        'label' => '帳戶選單',
    ],

    'footer' => [
        'copyright' => '© :year :brand 版權所有。',
        'help' => '說明與支援',
        'privacy' => '隱私權',
        'terms' => '服務條款',
    ],

    'breadcrumb' => [
        'home' => '首頁',
        'aria_label' => '麵包屑導覽',
    ],

    'components' => [
        'page_header' => [
            'actions' => '頁面操作',
        ],
        'empty_state' => [
            'default_title' => '尚無內容',
            'default_description' => '建立第一個項目以開始使用。',
        ],
        'loading_state' => [
            'default_message' => '載入中…',
            'aria_label' => '內容載入中',
        ],
        'page_loader' => [
            'content_message' => '正在載入內容…',
            'message' => '正在更新語言…',
            'aria_label' => '頁面載入中',
        ],
    ],

    'dashboard' => [
        'title' => '儀表板',
        'subtitle' => '列印工作區概覽',
        'welcome' => '歡迎回來，:name。',
        'merchant_account' => '商家帳戶：:name',
        'cards' => [
            'new_upload' => [
                'title' => '新增上傳',
                'description' => '上傳 PDF、CSV 或 XLSX 進行處理。',
            ],
            'upload_history' => [
                'title' => '上傳紀錄',
                'description' => '追蹤狀態並檢視已上傳的檔案。',
            ],
            'printing' => [
                'title' => '列印模組',
                'description' => '開啟工作區以預覽並列印履單文件。',
            ],
        ],
        'stats' => [
            'recent_uploads' => '近期上傳',
            'pending_jobs' => '待處理工作',
            'completed_jobs' => '已完成工作',
        ],
    ],

    'uploads' => [
        'title' => '上傳紀錄',
        'create_title' => '新增上傳',
        'show_title' => '上傳 #:id',
        'subtitle' => '管理檔案上傳與處理狀態',
        'create_subtitle' => '選擇類型並拖放檔案',
        'show_subtitle' => '上傳詳情與檔案清單',
        'new_upload' => '新增上傳',
        'back_to_history' => '返回紀錄',
        'empty' => [
            'title' => '尚無上傳',
            'description' => '上傳第一個檔案以開始處理。',
            'action' => '上傳第一個檔案',
        ],
        'table' => [
            'id' => '編號',
            'type' => '類型',
            'status' => '狀態',
            'files' => '檔案',
            'uploaded_by' => '上傳者',
            'date' => '日期',
            'actions' => '操作',
            'view' => '檢視',
        ],
        'form' => [
            'type_label' => '上傳類型',
            'type_placeholder' => '選擇類型…',
            'accepted_pdf' => '接受格式：PDF',
            'accepted_spreadsheet' => '接受格式：CSV、XLS、XLSX',
            'dropzone_title' => '拖放檔案至此，或',
            'dropzone_browse' => '瀏覽',
            'dropzone_limits' => '最多 :count 個檔案 · 每個 :size MB',
            'uploading' => '上傳中…請稍候。',
            'cancel' => '取消',
            'submit' => '上傳檔案',
        ],
        'detail' => [
            'type' => '類型',
            'status' => '狀態',
            'uploaded_by' => '上傳者',
            'file_count' => '檔案數量',
            'pdf_files' => 'PDF 檔案',
            'spreadsheet_files' => '試算表檔案',
            'preview_placeholder' => '預覽顯示於右側面板。',
        ],
        'preview' => [
            'heading' => '上傳預覽',
            'description' => '此上傳工作的 :width×:height mm 列印預覽。',
            'refresh' => '重新整理預覽',
            'refreshing' => '正在重新整理…',
            'retry' => '重試',
            'unavailable' => '此上傳尚無可用預覽。',
            'error_title' => '無法載入預覽',
            'empty_title' => '預覽尚未就緒',
            'empty_description' => '處理流程尚未產生此工作的可列印預覽。',
        ],
        'status' => [
            'pending' => '待處理',
            'processing' => '處理中',
            'completed' => '已完成',
            'failed' => '失敗',
            'cancelled' => '已取消',
        ],
        'types' => [
            'order_pdf' => '訂單 PDF',
            'thermal_label' => '熱感標籤',
            'picking_list' => '揀貨單',
            'delivery_label' => '出貨標籤',
        ],
        'validation' => [
            'type_required' => '請選擇上傳類型。',
            'type_invalid' => '所選上傳類型無效。',
            'files_required' => '請至少新增一個檔案。',
            'files_max' => '一次最多可上傳 :max 個檔案。',
            'file_missing' => '缺少一個或多個檔案。',
            'file_invalid' => '每個項目必須是有效檔案。',
            'file_type_invalid' => '此檔案類型不適用於所選上傳類型。',
            'file_too_large' => '每個檔案不得大於 :max KB。',
        ],
        'errors' => [
            'no_merchant_profile' => '您的帳號尚未連結商家資料，請聯絡支援人員。',
        ],
    ],

    'profile' => [
        'title' => '個人資料',
        'subtitle' => '管理您的帳戶設定',
        'information' => [
            'title' => '個人資訊',
            'description' => '更新您的個人資料與電子郵件地址。',
            'name' => '姓名',
            'name_placeholder' => '請輸入您的姓名',
            'email' => '電子郵件',
            'email_placeholder' => 'you@shop.example',
            'unverified' => '您的電子郵件尚未驗證。',
            'resend_verification' => '點此重新發送驗證郵件。',
            'verification_sent' => '新的驗證連結已發送至您的電子郵件。',
            'save' => '儲存',
            'saved' => '已儲存。',
        ],
        'password' => [
            'title' => '更新密碼',
            'description' => '請使用長且隨機的密碼以確保帳戶安全。',
            'current' => '目前密碼',
            'current_placeholder' => '請輸入目前密碼',
            'new' => '新密碼',
            'new_placeholder' => '請輸入新密碼',
            'confirm' => '確認密碼',
            'confirm_placeholder' => '請再次輸入新密碼',
            'save' => '儲存',
            'saved' => '已儲存。',
        ],
        'photo' => [
            'title' => '個人照片',
            'description' => '上傳照片以個人化您的帳戶，照片會顯示在頂部導覽列與側邊欄。',
            'upload' => '上傳照片',
            'remove' => '移除照片',
            'hint' => '支援 JPEG、PNG 或 WebP，最大 2 MB。儲存前可裁切。',
            'crop_title' => '裁切個人照片',
            'crop_description' => '拖曳調整位置，並使用控制點調整裁切範圍。',
            'cancel' => '取消',
            'save' => '儲存照片',
            'saving' => '儲存中…',
            'updated' => '個人照片已更新。',
            'removed' => '個人照片已移除。',
        ],
        'delete' => [
            'title' => '刪除帳戶',
            'description' => '刪除帳戶後，所有相關資源與資料將永久移除。刪除前請先下載您需要保留的資料。',
            'button' => '刪除帳戶',
            'confirm_title' => '確定要刪除帳戶嗎？',
            'confirm_text' => '刪除後所有資料將永久移除。請輸入密碼以確認。',
            'password' => '密碼',
            'confirm_button' => '刪除帳戶',
            'cancel' => '取消',
        ],
    ],

    'printing' => [
        'section_title' => '列印模組',
        'dashboard_description' => '選擇模組以在列印前預覽標籤、訂單與揀貨單。',
        'modules_available' => '{1} :count 個模組可用|[2,*] :count 個模組可用',
        'nav_none_enabled' => '此區域尚未啟用任何列印模組。',
        'errors' => [
            'module_disabled' => '您的區域尚未啟用此列印模組。',
        ],
        'workspace' => [
            'list_heading' => '項目列表',
            'list_description' => '選取項目以預覽可列印內容。',
            'list_empty' => '目前沒有可用的項目。',
            'preview_heading' => '預覽',
            'preview_description' => '150×100 mm 列印預覽工作區，含安全列印區。',
            'preview_placeholder' => '請從列表選取項目以檢視可列印預覽。',
            'preview_not_implemented' => '選取列表項目以載入即時預覽。',
            'print' => '列印',
            'print_disabled_hint' => '請選取項目以啟用列印。',
            'status_pending' => '待處理',
            'status_processing' => '處理中',
            'status_ready' => '就緒',
            'placeholder_item_title' => '範例項目',
            'placeholder_item_subtitle' => '1500×1000 px — 符合 3:2 列印比例。',
            'placeholder_item_invalid_title' => '範例項目（比例不符）',
            'placeholder_item_invalid_subtitle' => '800×600 px — 會觸發長寬比警告。',
        ],
        'preview' => [
            'not_found' => '無法載入預覽內容。',
            'order_details' => [
                'fields' => [
                    'customer' => '顧客',
                    'date' => '訂單日期',
                    'sku' => 'SKU',
                    'item' => '品項',
                    'qty' => '數量',
                    'price' => '價格',
                    'subtotal' => '小計',
                    'shipping' => '運費',
                    'total' => '合計',
                ],
                'samples' => [
                    'list_title' => '訂單 #:id',
                    'list_subtitle' => 'Shopee 訂單 — 可列印摘要',
                    'list_subtitle_alt' => '第二筆範例訂單',
                    'order_number' => 'SO-2026-:id',
                    'customer_name' => '陳偉霖',
                    'order_date' => '2026-05-28',
                    'status' => '可列印',
                    'item_one' => '無線耳機保護殼',
                    'item_two' => 'USB-C 充電線',
                    'notes' => '需要禮品包裝，附發票副本。',
                ],
            ],
            'logistics_labels' => [
                'fields' => [
                    'carrier' => '物流商',
                    'tracking' => '追蹤號碼',
                    'shipment_date' => '出貨日期',
                    'service_level' => '服務',
                ],
                'samples' => [
                    'list_title' => '物流標籤範例',
                    'list_subtitle' => '含條碼區的熱感標籤',
                    'tracking_number' => 'TW:id234567890',
                    'carrier' => '黑貓宅急便',
                    'recipient_name' => '林美華',
                    'recipient_address' => '台北市忠孝東路 88 號 100',
                    'shipment_date' => '2026-05-29',
                    'service_level' => '標準宅配',
                ],
            ],
            'picking_list' => [
                'fields' => [
                    'sku' => 'SKU',
                    'item' => '品項',
                    'location' => '儲位',
                    'qty' => '數量',
                    'total_units' => '總件數',
                ],
                'samples' => [
                    'list_title' => '揀貨單 PL-:id',
                    'list_subtitle' => '倉儲揀貨表預覽',
                    'list_reference' => 'PL-2026-:id',
                    'warehouse' => '台北主倉',
                    'pick_date' => '2026-05-29',
                    'item_one' => '無線耳機保護殼',
                    'item_two' => 'USB-C 充電線',
                    'item_three' => '螢幕保護貼組',
                ],
            ],
        ],
        'modules' => [
            'order_details' => [
                'title' => '訂單明細',
                'subtitle' => '檢視訂單 PDF 並準備可列印輸出。',
            ],
            'logistics_labels' => [
                'title' => '物流標籤',
                'subtitle' => '標準化並列印 Shopee 物流標籤。',
            ],
            'picking_list' => [
                'title' => '揀貨單',
                'subtitle' => '彙整倉儲揀貨清單。',
            ],
            'delivery_labels' => [
                'title' => '出貨標籤',
                'subtitle' => '產生出貨配送標籤。',
            ],
        ],
    ],

    'flash' => [
        'upload_received' => '檔案已成功接收，即將開始處理。',
        'profile_updated' => '您的個人資料已更新。',
        'locale_updated' => '語言偏好已更新。',
        'theme_updated' => '主題偏好已更新。',
        'success' => '成功',
        'error' => '錯誤',
        'warning' => '警告',
        'info' => '資訊',
    ],

    'ajax' => [
        'error_default' => '發生錯誤，請再試一次。',
        'network_error' => '網路錯誤，請檢查連線後再試。',
    ],

    'sweetalert' => [
        'confirm' => '確認',
        'cancel' => '取消',
        'ok' => '確定',
    ],

    'preview' => [
        'dimensions_label' => ':width×:height mm',
        'toolbar' => [
            'heading' => '預覽',
            'description' => '固定 150×100 mm 列印區域（3:2 比例），會依螢幕大小等比例縮放。',
            'print' => '列印',
            'print_disabled_hint' => '請先選取項目以啟用列印。',
            'safe_zone_disabled_hint' => '請先選取項目以切換安全區。',
        ],
        'container' => [
            'aria_label' => '列印預覽畫布，150×100 公釐',
        ],
        'safe_zone' => [
            'aria_label' => '安全列印區，各邊內縮 :inset 公釐',
            'toggle_show' => '顯示安全區',
            'toggle_hide' => '隱藏安全區',
            'description' => '虛線標示可列印安全區域，各邊內縮 :inset mm。',
        ],
        'empty' => [
            'title' => '請從列表選取項目以預覽',
            'description' => '預覽畫布為固定 150×100 mm 工作區，並依螢幕等比例縮放。',
            'selected_fallback' => '預覽項目',
            'content_placeholder' => '請從列表選取項目以檢視可列印預覽。',
            'list_hint' => '請從左側列表選取項目',
        ],
        'aspect_ratio' => [
            'valid' => '素材尺寸符合 150×100 mm（3:2）列印比例。',
            'invalid' => '長寬比偏離 3:2 達 :deviation%（容許值 :tolerance%）。',
            'banner_title' => '長寬比警告',
            'banner_message' => '此素材不符合所需的 150×100 mm（3:2）列印比例。',
            'force_adjustment' => '強制調整（仍要繼續）',
            'sweetalert_title' => '長寬比不符',
            'sweetalert_message' => '所選素材超出 150×100 mm 列印比例的允許偏差。',
            'validation' => [
                'width_required' => '未上傳檔案時必須提供寬度。',
                'height_required' => '未上傳檔案時必須提供高度。',
                'file_or_dimensions_required' => '請提供寬度與高度，或上傳圖片檔案。',
                'unsupported_file' => '僅支援圖片檔案（JPG、PNG、GIF、WebP、BMP）。',
            ],
        ],
    ],

    'delivery_labels' => [
        'preview' => [
            'remarks_heading' => '備註',
            'shrunk_hint' => '地址已自動縮小以符合標籤',
        ],
        'csv' => [
            'list_description' => '上傳 CSV 或選取標籤以預覽。',
            'upload_label' => '匯入配送標籤（CSV）',
            'choose_file' => '選擇 CSV 檔案',
            'upload_hint' => '必要欄位：收件人及/或地址。選填：備註、追蹤號碼、物流商。',
            'uploading' => '正在匯入 CSV…',
            'list_empty' => '尚無配送標籤。請上傳 CSV 開始使用。',
            'list_subtitle' => '標籤 #:id',
            'unknown_recipient' => '未知收件人',
            'fallback_address' => '未提供地址',
            'confirm_title' => '匯入配送標籤？',
            'confirm_message' => '系統將解析 CSV 並加入您的工作區列表。',
            'import_success' => '已成功匯入 :count 筆配送標籤。',
            'validation' => [
                'file_required' => '請選擇要上傳的 CSV 檔案。',
                'file_type' => '僅支援 CSV 檔案。',
                'file_too_large' => 'CSV 檔案過大。',
                'headers_missing' => 'CSV 檔案缺少標題列。',
                'columns_missing' => '無法在 CSV 中偵測收件人或地址欄位。',
                'rows_missing' => 'CSV 檔案沒有資料列。',
                'no_valid_rows' => 'CSV 中找不到有效的配送標籤資料列。',
            ],
        ],
        'samples' => [
            'short_title' => '標準地址',
            'short_subtitle' => '18 px — 35 字以內',
            'short_recipient' => '陳偉霖',
            'short_address' => '台北市忠孝東路 88 號',
            'short_remarks' => '若無人在家請交管理室。',

            'long_title' => '長篇快遞地址',
            'long_subtitle' => '自動縮小至 14 px 下限',
            'long_recipient' => '林美華',
            'long_address' => '台北市信義區信義路五段 188 號 110 — 請送至 B 區停車場後門，抵達時聯絡保全櫃台',
            'long_remarks' => '易碎物品。送達前 10 分鐘請致電收件人。',

            'multiline_title' => '多行地址',
            'multiline_subtitle' => '自動換行，備註區下推',
            'multiline_recipient' => '王家豪',
            'multiline_address' => "台北市信義區基隆路 200 號 12 樓\n110 信義區",
            'multiline_remarks' => '收件時間：週一至週五 09:00–18:00。',
        ],
    ],

];
    