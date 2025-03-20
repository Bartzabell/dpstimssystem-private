<script setup>
  import { ref, watch, computed } from 'vue';

  const props = defineProps({
    transactionId: {
      type: Number,
      default: null
    }
  });

  const transaction = ref(null);

  async function printTest(billId) {
    if (billId) {
      const url = route('sales.show', billId);

      try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch transaction data');

        transaction.value = await response.json();
        setTimeout(() => {
          printContent();
        }, 300);
      } catch (error) {
        console.error('Error fetching transaction data:', error);
      }
    } else {
      printContent();
    }
  }

  function printContent() {
    const printContent = document.getElementById('printSection').innerHTML;
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);

    iframe.contentDocument.write(`
      <html>
      <head>
        <title>Sales Invoice</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
          @page {
            size: A4;
            margin: 20mm;
          }

          @media print {
            body {
              width: 210mm;
              height: 297mm;
            }
          }
        </style>
      </head>

      <body>
        ${printContent}
      </body>

      </html>
    `);

    iframe.contentDocument.close();

    iframe.onload = function() {
      iframe.contentWindow.print();
      setTimeout(() => {
        document.body.removeChild(iframe);
      }, 1000);
    };
  }

  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  function calculateItemTotal(qty, price) {
    return (parseFloat(qty) * parseFloat(price)).toFixed(2);
  }

  function formatDiscount(discount) {
    if (!discount) return '0.00';

    const amount = parseFloat(discount.amount);
    if (isNaN(amount)) return '0.00';

    if (discount.type === 'Fixed Amount') {
      return amount.toFixed(2);
    } else if (discount.type === 'Percentage') {
      return `${amount}%`;
    }

    return '0.00';
  }

  const totalAmount = computed(() => {
    if (!transaction.value || !transaction.value.items) return 0;
    return transaction.value.items.reduce((sum, item) => sum + parseFloat(item.item_price), 0).toFixed(2);
  });

  defineExpose({ printTest });
</script>

<template>
  <div id="printSection" class="hidden">
    <!-- Your updated print section template with real data -->
    <div class="max-w-[210mm] h-full mx-auto">
      <h1 class="w-full mb-1 text-4xl font-bold text-center">
        DELLOSA'S SOAP AND DETERGENTS MANUFACTURING
      </h1>
      <p class="w-full text-lg text-center"><b>DEALERS IN: </b>Products</p>
      <p class="w-full text-lg text-center">Zenaida subdivision, Limaco street, 3930 Brgy, Biñan, 4024 Laguna</p>
      <p class="w-full text-lg text-center">EMAIL: dellosaspm@gmail.com</p>
      <div class="grid grid-cols-4 mt-5 border border-black">
        <div class="col-span-2 p-1 font-bold border border-black">
          NAME OF CONSIGNEE/BUYER
        </div>
        <div class="p-1 font-bold border border-black">
          Invoice No.:
        </div>
        <div class="p-1 border border-black">
          <!-- for invoice -->
        </div>
        <div class="col-span-2 p-1 border border-black">
          {{ transaction?.customer?.name || 'N/A' }}
        </div>
        <div class="p-1 font-bold border border-black">
          Date:
        </div>
        <div class="p-1 border border-black">
          {{ transaction ? formatDate(transaction.date_sold) : 'N/A' }}
        </div>
        <div class="flex items-center justify-center w-full col-span-2 row-span-3 p-1 border border-black">
          <!-- for signature -->
        </div>
        <div class="p-1 font-bold border border-black">
          Terms:
        </div>
        <div class="p-1 border border-black">
          <!-- for payment terms -->
        </div>
        <div class="p-1 font-bold border border-black">
          VEH No.:
        </div>
        <div class="p-1 border border-black">
          <!-- for vehicle number -->
        </div>
        <div class="p-1 font-bold border border-black">
          Destination:
        </div>
        <div class="p-1 border border-black">
          {{ transaction?.customer?.street || '' }}, {{ transaction?.customer?.municipality || '' }}, {{ transaction?.customer?.city || '' }}
        </div>
        <div class="col-span-2 p-1 border border-black">
          <b>TIN: </b>{{ transaction?.customer?.tin_no || 'N/A' }}
        </div>
        <div class="p-1 font-bold border border-black">
          Business Type:
        </div>
        <div class="p-1 border border-black">
          <!-- for business type -->
        </div>
      </div>
      <div class="grid grid-cols-10 mt-0.5 border border-black">
        <div class="p-1 font-bold text-center border border-black">
          #
        </div>
        <div class="col-span-3 p-1 font-bold text-center border border-black">
          ITEM/S
        </div>
        <div class="col-span-2 p-1 font-bold text-center border border-black">
          QTY
        </div>
        <div class="col-span-2 p-1 font-bold text-center border border-black">
          Unit Price
        </div>
        <div class="col-span-2 p-1 font-bold text-center border border-black">
          Amount
        </div>
        <!-- Items Body -->
        <template v-if="transaction && transaction.items && transaction.items.length > 0">
          <template v-for="(item, index) in transaction.items" :key="item.id">
            <div class="p-1 text-center border border-black">
              {{ index + 1 }}
            </div>
            <div class="col-span-3 p-1 text-center border border-black">
              {{ item.stock?.item_code || 'N/A' }}
            </div>
            <div class="col-span-2 p-1 text-center border border-black">
              {{ item.item_qty }}
            </div>
            <div class="col-span-2 p-1 text-center border border-black">
              {{ parseFloat(item.stock?.price).toFixed(2) }}
            </div>
            <div class="col-span-2 p-1 text-center border border-black">
              {{ parseFloat(item.item_price).toFixed(2) }}
            </div>
          </template>
        </template>
        <template v-else>
          <div class="p-1 text-center border border-black">
            -
          </div>
          <div class="col-span-3 p-1 text-center border border-black">
            No items
          </div>
          <div class="col-span-2 p-1 text-center border border-black">
            -
          </div>
          <div class="col-span-2 p-1 text-center border border-black">
            -
          </div>
          <div class="col-span-2 p-1 text-center border border-black">
            -
          </div>
        </template>
      </div>
      <div class="grid grid-cols-10 mt-0.5 border border-black">
        <div class="col-span-4 p-1 font-bold border border-black">
          Total Sales(VAT Inclusive)
        </div>
        <div class="col-span-6 p-1 border border-black">
          <!-- {{ transaction?.total_price ? parseFloat(transaction.total_price).toFixed(2) : '0.00' }} -->
        </div>
        <div class="col-span-4 p-1 font-bold border border-black">
          Less VAT
        </div>
        <div class="col-span-6 p-1 border border-black">
          <!-- {{ transaction?.discount?.tax_amount ? parseFloat(transaction.discount.tax_amount).toFixed(2) : '0.00' }} -->
        </div>
        <div class="col-span-4 p-1 font-bold border border-black">
          Amount: Net of VAT
        </div>
        <div class="col-span-6 p-1 border border-black">
          <!-- {{
            transaction?.total_price ?
            (parseFloat(transaction.total_price) - (parseFloat(transaction?.discount?.tax_amount || 0))).toFixed(2) :
            '0.00'
          }} -->
        </div>
        <div class="col-span-4 p-1 font-bold border border-black">
          Less: {{transaction?.discount?.name || ''}}
        </div>
        <div class="col-span-6 p-1 border border-black">
          {{ formatDiscount(transaction?.discount) }}
        </div>
        <div class="col-span-4 p-1 font-bold border border-black">
          Amount Due
        </div>
        <div class="col-span-6 p-1 border border-black">
          {{
            totalAmount ?
            (parseFloat(totalAmount) - parseFloat(transaction?.discount?.tax_amount || 0) - parseFloat(transaction?.discount?.discount_amount || 0)).toFixed(2) :
            '0.00'
          }}
        </div>
        <div class="col-span-4 p-1 font-bold border border-black">
          Total Due:
        </div>
        <div class="col-span-6 p-1 border border-black">
          {{ transaction?.total_price ? parseFloat(transaction.total_price).toFixed(2) : '0.00' }}
        </div>
      </div>
    </div>
    <div class="flex justify-end w-full mx-auto mt-auto">
      <!-- name of buyer/consignee -->
      <h1 class="px-5 pt-1 border-t border-black">{{ transaction?.customer?.name || 'N/A' }}</h1>
    </div>
  </div>
</template>
