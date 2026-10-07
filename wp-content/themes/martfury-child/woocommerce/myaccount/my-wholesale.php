<style>
  .inq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .inq-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 10px 16px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px 20px;
    align-items: center;
    cursor: pointer;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
  }
  .inq-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .inq-title {
    font-size: 14px;
    font-weight: 500;
    color: #2563eb;
    line-height: 1.45;
    margin-bottom: 7px;
  }

  .inq-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .inq-agent {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6b7280;
  }
  .avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .avatar svg {
    width: 13px;
    height: 13px;
    stroke: #9ca3af;
    fill: none;
  }
  .meta-sep {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #d1d5db;
    flex-shrink: 0;
  }
  .inq-date {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #9ca3af;
  }
  .inq-date svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
  }

  /* Right side */
  .inq-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    flex-shrink: 0;
  }

  /* Status badges */
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 500;
    padding: 3px 9px;
    border-radius: 999px;
    white-space: nowrap;
  }
  .badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .badge-open {
    background: #ecfdf5;
    color: #065f46;
  }
  .badge-open .badge-dot {
    background: #10b981;
  }
  .badge-pending {
    background: #fffbeb;
    color: #92400e;
  }
  .badge-pending .badge-dot {
    background: #f59e0b;
  }

  /* View button */
  .view-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 500;
    color: #2563eb;
    background: none;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    padding: 5px 11px;
    cursor: pointer;
    transition: background 0.15s;
    white-space: nowrap;
  }
  .view-btn:hover {
    background: #eff6ff;
  }
  .view-btn svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
  }

  /* Responsive */
  @media (max-width: 560px) {
    .content {
        padding: 1rem;
    }
    .inq-card {
        grid-template-columns: 1fr;
    }
    .inq-right {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    .toolbar {
        flex-direction: column;
    }
  }
</style>
<div class="my-account-header">
	<h2>Messages</h2>
  <div class="my-account-header-tags">Manage your messages and inquiries</div>
</div>
<div class="wp-tab-container inquries-section">
  
  <input type="radio" name="wp-tabs" id="tab-inquiries" class="wp-tab-input" checked>
  <label for="tab-inquiries" class="wp-tab-label">My Inquiries</label>
  <div id="content-inquiries" class="wp-tab-content">
    <div class="inq-list" id="inq-list">
      <?php if($inquiries): ?>
        <?php foreach($inquiries as $row): ?>
          <?php
            // Get the permalink for the specific product in this row
            $product_post = get_post($row->product_id);
            $product_url = get_permalink($row->product_id);

            // Nonce for secure deletion
            $delete_url = wp_nonce_url(
                admin_url("admin.php?page=partner-inquiries&partner_id=$partner_id&action=delete&inquiry_id={$row->id}"),
                'delete_inquiry_' . $row->id
            );
          ?>
          <div class="inq-card" data-search="high quality unbreakable melamine cutlery thick black matte plate sets dining room">
            <div>
              <div class="inq-title">
                <a href="<?php echo esc_url($product_url); ?>" target='_blank' >
                  <?php echo esc_html($product_post->post_title); ?>
                </a>
              </div>
              <div class="inq-meta">
                  <div class="inq-agent">
                    <?php if(!$row->agent_id): ?>
                      <div class="avatar">
                          <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                          </svg>
                      </div>
                    <?php else: ?>
                      <?php echo sls_get_user_agent($row->agent_id); ?>
                    <?php endif; ?>
                  </div>
                  <span class="meta-sep"></span>
                  <div class="inq-date">
                    <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <?php echo date('M j, Y', strtotime($row->created_at)); ?>
                  </div>
              </div>
            </div>
            <div class="inq-right">
              <span class="badge badge-open"><span class="badge-dot"></span><?php echo esc_attr($row->status) ?></span>
              <button class="view-btn trigger-btn" data-product-id='<?php echo esc_attr($row->product_id) ?>' data-inquiry-id="<?php echo esc_attr($row->id); ?>" data-inquiry-type="inquiry">
                  <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" /></svg>
                  View
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="woocommerce-info">
          You have not made any inquiries yet. Browse our products and click "Inquire" to start a conversation with our agents!
        </div>
      <?php endif; ?>
    </div>
  </div>

  <input type="radio" name="wp-tabs" id="tab-rfqs" class="wp-tab-input">
  <label for="tab-rfqs" class="wp-tab-label">My RFQs</label>
  <div id="content-rfqs" class="wp-tab-content">
    <div class="inq-list">
        <?php foreach($rfqs as $rfq): ?>
          <?php
            $product_url = get_permalink($rfq->product_id);  
          ?>
          <div class="inq-card" data-search="high quality unbreakable melamine cutlery thick black matte plate sets dining room">
            <div>
              <div class="inq-title">
                <a href="<?php echo $product_url; ?>" target='_blank'>
                  #RFQ<?php echo $rfq->product_id; ?>
                </a>
              </div>
              <div class="inq-meta">
                <div class="inq-date">
                  <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10" />
                      <polyline points="12 6 12 12 16 14" />
                  </svg>
                  <?php echo date('M j, Y', strtotime($rfq->created_at)); ?>
                </div>
              </div>
            </div>
            <div class="inq-right">
              <span class="badge badge-open"><span class="badge-dot"></span><?php echo $rfq->status; ?></span>
              <button class="view-btn trigger-btn" data-product-id='<?php echo esc_attr($row->product_id) ?>' data-inquiry-id="<?php echo esc_attr($row->id); ?>" data-inquiry-type="inquiry">
                  <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" /></svg>
                  View
              </button>
            </div>
          </div>
        <?php endforeach; ?>
    </div>
  </div>
</div>

</div>

<div id="popup" class="popup">
    <button id="closeBtn" class="close-icon" aria-label="Close">&times;</button>
    
    <div class="popup-content">
        <h2>Product Details</h2>
        <hr>
        <p>You are viewing details for Product ID: <strong><span id="display-id">---</span></strong></p>
        <div id="inquiry-details">

        </div>
    </div>
</div>