 <html>
        <body style='font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #eee;'>
                
                <div style='background-color: #2eb82e; color: #ffffff; padding: 30px; text-align: center;'>
                    <h2 style='margin: 0;'>Inquiry Confirmed</h2>
                    <p style='margin: 5px 0 0;'>Thank you for choosing Anythingsupplies</p>
                </div>

                <div style='padding: 30px;'>
                    <p>Hi <strong><?php echo $customer_name; ?></strong>,</p>
                    <p>We've successfully received your inquiry for the following product. One of our agents has been assigned to your request and will contact you shortly with a formal quotation.</p>

                    <table style='width: 100%; margin: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0;'>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Product:</td>
                            <td style='font-weight: bold; text-align: left;'><?php echo $product_name; ?></td>
                        </tr>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Quantity:</td>
                            <td style='font-weight: bold; text-align: left;'><?php echo $qty; ?> units</td>
                        </tr>
                        <tr>
                            <td style='color: #777; padding: 5px 0;'>Inquiry Status:</td>
                            <td style='font-weight: bold; text-align: left; color: #2eb82e;'>Processing</td>
                        </tr>
                    </table>

                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; font-size: 14px; color: #856404;'>
                        <strong>Next Step:</strong> Your assigned agent will review your requirements and reach out via email within 24-48 business hours.
                    </div>

                    <p style='margin-top: 25px;'>If you have any questions in the meantime, please feel free to reply to this email.</p>
                </div>

                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999;'>
                    &copy; 2026 Anythingsupplies.com | All Rights Reserved.<br>
                    You received this email because you submitted an inquiry on our portal.
                </div>
            </div> 
        </body>
        </html>