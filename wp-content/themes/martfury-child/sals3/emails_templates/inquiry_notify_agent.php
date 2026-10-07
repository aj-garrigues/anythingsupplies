
<html>
<body style='font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
    <div style='max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
        
        <div style='background-color: #2c3e50; color: #ffffff; padding: 25px; text-align: center;'>
            <h1 style='margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;'>New Order Inquiry</h1>
            <p style='margin: 5px 0 0; opacity: 0.8;'>Priority: <span style='color: #ff1900; font-weight: bold;'>High</span></p>
        </div>

        <div style='background-color: #fff3cd; color: #856404; padding: 15px; text-align: center; font-weight: bold; border-bottom: 1px solid #ffeeba;'>
            ACTION REQUIRED: Please respond to this inquiry immediately.
        </div>

        <div style='padding: 30px; background-color: #ffffff;'>
            <p>Hello <strong><?php echo $databody['agent_name']; ?></strong>,</p>
            <p>A new high-volume inquiry has been assigned to you. Please review the details below and provide a quotation as soon as possible.</p>

            <div style='background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; margin-bottom: 25px;'>
                <h3 style='margin-top: 0; color: #2c3e50;'>Product Information</h3>
                <p style='margin: 5px 0;'><strong>Product:</strong> <?php echo $databody['product_name']; ?></p>
                <p style='margin: 5px 0;'><strong>Product ID:</strong> <?php echo $databody['product_id']; ?></p>
                <p style='margin: 5px 0; font-size: 18px; color: #27ae60;'><strong>Quantity Requested:</strong> <?php echo $databody['qty']; ?> units</p>
            </div>

            <h3 style='color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;'>Customer Details</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d; width: 40%;'>Customer Name:</td>
                    <td style='padding: 8px 0; font-weight: bold;'><?php echo $databody['customer_name']; ?></td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Email:</td>
                    <td style='padding: 8px 0;'><a href='mailto:<?php echo $databody['customer_email']; ?>' style='color: #3498db;'><?php echo $databody['customer_email']; ?></a></td>
                </tr>
                
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Contact:</td>
                    <td style='padding: 8px 0;'><?php echo $databody['customer_contact']; ?></td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; color: #7f8c8d;'>Preferences:</td>
                    <td style='padding: 8px 0;'>
                        <span style='font-size: 12px; background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 10px;'>Agreed to Related</span>
                        <span style='font-size: 12px; background: #e3f2fd; color: #1565c0; padding: 2px 8px; border-radius: 10px;'>Requested Quotations</span>
                    </td>
                </tr>
            </table>

            <div style='margin-top: 25px;'>
                <p style='color: #7f8c8d; margin-bottom: 5px;'>Customer Message:</p>
                <div style='padding: 15px; background: #ffffff; border: 1px solid #eee; font-style: italic; color: #555;'>
                    "<?php echo $databody['msg']; ?>"
                </div>
            </div>

            <div style='text-align: center; margin-top: 30px;'>
                <a href='mailto:<?php echo $databody['customer_email']; ?>?subject=Re: Inquiry for <?php echo $databody['product_name']; ?>' 
                   style='background-color: #27ae60; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                   Reply to Customer Now
                </a>
            </div>
        </div>

        <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #95a5a6;'>
            This is an automated priority alert from the <strong>Anythingsupplies Portal</strong>.<br>
            Agent Assignment ID: <?php echo $databody['agent_id']; ?>
        </div>
    </div>
</body>
</html>