# PayFast Integration Setup Guide

## 🎯 PayFast Integration Options for PictureThis

Since you're already registered with PayFast, here are the **3 integration options** available:

### **Option 1: Hosted Payment Page (Recommended)**
✅ **Easiest to implement**  
✅ **Most secure** (PayFast handles PCI compliance)  
✅ **Professional checkout experience**  
✅ **Already implemented in your code**

### **Option 2: API Integration**
🔧 **More control over payment flow**  
🔧 **Custom payment forms**  
🔧 **Advanced features** (subscriptions, etc.)  
❌ **More complex to implement**

### **Option 3: PayFast Buttons**
⚡ **Quick HTML integration**  
⚡ **Minimal coding required**  
❌ **Less customization**  
❌ **Basic functionality only**

---

## 🚀 **Current Implementation: Hosted Payment Page**

Your application now includes a complete PayFast integration using the **Hosted Payment Page** approach.

### **What You Need to Do:**

#### 1. **Get Your PayFast Credentials**
Log into your PayFast merchant account and get these values:
- **Merchant ID**
- **Merchant Key**
- **Passphrase** (create one in Settings > Integration)

#### 2. **Configure Environment Variables**
Update your `backend/.env` file:
```env
# PayFast Configuration
PAYFAST_MERCHANT_ID=your_payfast_merchant_id
PAYFAST_MERCHANT_KEY=your_payfast_merchant_key
PAYFAST_PASSPHRASE=your_payfast_passphrase
```

#### 3. **Set Up PayFast Settings**
In your PayFast merchant dashboard:
- **Return URL**: `http://localhost:3000/dashboard?payment=success`
- **Cancel URL**: `http://localhost:3000/dashboard?payment=cancelled`
- **Notify URL**: `http://localhost:3010/api/credits/payfast/notify`
- **Enable ITN (Instant Transaction Notification)**

#### 4. **Create Database Table**
Run this SQL in your PostgreSQL database:
```sql
-- Run the migration script
\i backend/src/utils/create_credit_transactions_table.sql
```

#### 5. **Test the Integration**
1. Start your servers:
   ```bash
   # Backend
   cd backend && npm start

   # Frontend
   cd picfe && npm run dev
   ```

2. Visit: `http://localhost:3000/credits`
3. Try purchasing credits with PayFast

---

## 📋 **Credit Packages Included**

| Package | Credits | Price (ZAR) | Savings |
|---------|---------|-------------|---------|
| Small | 50 | R50.00 | - |
| Medium | 150 | R135.00 | Save 10% |
| Large | 300 | R240.00 | Save 20% |
| Premium | 500 | R350.00 | Save 30% |

---

## 🔧 **Features Included**

✅ **Secure PayFast integration**  
✅ **Automatic credit crediting**  
✅ **Transaction history**  
✅ **Payment verification**  
✅ **Error handling**  
✅ **Mobile responsive**  
✅ **South African pricing**  

---

## 🐛 **Troubleshooting**

### **Common Issues:**

1. **"Invalid signature" error**
   - Check your passphrase is correct
   - Ensure no extra spaces in credentials

2. **Payments not processing**
   - Verify PayFast credentials
   - Check ITN is enabled
   - Confirm notify URL is accessible

3. **Credits not added**
   - Check database connection
   - Verify ITN webhook is working
   - Check server logs for errors

### **Testing in Sandbox:**
- Use PayFast's sandbox environment for testing
- Use test credentials from PayFast dashboard
- Test with small amounts first

---

## 📞 **Need Help?**

- **PayFast Documentation**: https://developers.payfast.co.za/
- **PayFast Support**: support@payfast.co.za
- **Test Credentials**: Available in PayFast merchant dashboard

---

## 🎉 **Ready to Go!**

Once you've configured the environment variables and database, your PayFast integration will be live at:
**`http://localhost:3000/credits`**

The integration handles everything automatically:
- Payment processing through PayFast
- Secure credit crediting
- Transaction logging
- User notifications
