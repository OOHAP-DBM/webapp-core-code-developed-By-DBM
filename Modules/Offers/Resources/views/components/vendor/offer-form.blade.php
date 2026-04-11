{{--
    offer-form.blade.php
    Props:
      $enquiry      — Enquiry model | null
      $enquiryItems — Collection | empty collection
      $enquiryItemsJson — JSON string (pre-encoded in controller)
--}}

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col" id="offer-form-root">

    {{-- ── Header ── --}}
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-shrink-0">
        <div>
            <h2 class="text-base font-bold text-gray-800">Create Offer</h2>
            <p class="text-xs text-gray-400 mt-0.5">Create an offer for customer review</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-xs font-semibold text-gray-500 whitespace-nowrap">Offer Valid till</label>
            <div class="relative">
                <input type="date" id="offerValidTill"
                    class="border border-gray-300 rounded px-3 py-1.5 text-xs text-gray-700 focus:ring-green-500 focus:border-green-500 outline-none"
                    placeholder="e.g 12Mar, 25">
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-5 flex-1 overflow-y-auto flex flex-col">

        {{-- ════════════════════════════════════════
             CUSTOMER SELECTOR
        ════════════════════════════════════════ --}}
        <div class="mb-5" id="offerCustomerSection">
            @if($enquiry)
                {{-- Enquiry mode: show locked customer --}}
                <div class="flex items-center gap-3 border border-blue-200 bg-blue-50 rounded-lg px-4 py-3">
                    <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-800">{{ $enquiry->customer->name ?? '—' }}</p>
                        <p class="text-[10px] text-gray-500">{{ $enquiry->customer->email ?? '' }}{{ ($enquiry->customer->phone ?? null) ? ' · '.$enquiry->customer->phone : '' }}</p>
                    </div>
                    <span class="text-[9px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                        Enquiry #{{ $enquiry->formatted_id ?? $enquiry->id }}
                    </span>
                </div>
                <input type="hidden" id="offerCustomerId" value="{{ $enquiry->customer_id ?? '' }}">
                <input type="hidden" id="offerCustomerName" value="{{ $enquiry->customer->name ?? '' }}">
            @else
                {{-- Direct mode: customer search --}}
                <div class="relative" id="offerCustomerSearch">
                    <input type="text" id="offerCustomerInput"
                        placeholder="Search customer by name, email, mobile number..."
                        autocomplete="off"
                        class="w-full border border-gray-300 rounded-lg pl-4 pr-20 py-2.5 text-xs focus:ring-green-500 focus:border-green-500 outline-none"
                        oninput="offerSearchCustomer(this.value)">
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                        <button onclick="offerClearCustomer()"
                            id="offerCustomerClearBtn"
                            class="hidden text-gray-400 hover:text-gray-600 px-1">✕</button>
                        <button onclick="offerNewCustomer()"
                            class="w-7 h-7 rounded-md bg-[#2D5A43] text-white flex items-center justify-center hover:bg-opacity-90"
                            title="Add new customer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                    <div id="offerCustomerDropdown"
                        class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    </div>
                </div>
                <input type="hidden" id="offerCustomerId" value="">
                <input type="hidden" id="offerCustomerName" value="">
                <input type="hidden" id="offerCustomerEmail" value="">
                <input type="hidden" id="offerCustomerPhone" value="">
                <input type="hidden" id="offerCustomerBusiness" value="">
                <input type="hidden" id="offerCustomerGstin" value="">
                <input type="hidden" id="offerCustomerAddress" value="">
            @endif
        </div>


        {{-- ════════════════════════════════════════
             EMPTY STATE
        ════════════════════════════════════════ --}}
        <div id="offerEmptyState" class="flex-1 flex flex-col items-center justify-center py-12 text-center">
            <div class="mb-4">
               <svg width="91" height="97" viewBox="0 0 91 97" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M48.2208 68.7847H17.1959L17.1035 69.3981L21.21 71.775H16.775L16.6826 72.3993L20.7891 74.8091H8.03828L8.72974 72.4464L2.64844 75.6196L7.94837 78.1645H9.27335L12.5555 76.6602H23.9101L47.071 90.1877H32.965L33.6529 88.8185L33.7658 87.4165L28.0577 91.1625L32.7494 94.3281H33.889L37.8723 92.0498H50.192L58.518 96.924H89.5429L89.6353 96.3106L85.8984 94.12V93.9338H90.0049L90.0973 93.3204L84.0709 89.7824H84.6459C86.114 89.7824 86.5554 89.399 86.6991 88.9061H87.2638L87.541 87.0988C87.541 86.9016 87.3254 86.584 86.9969 86.3868C86.8616 86.3027 86.7111 86.2505 86.5554 86.2335H86.1961C86.0626 86.2335 85.9805 86.2335 85.96 86.3759L85.4877 88.1832L86.0832 88.5337V88.7418C86.0216 89.1033 85.4261 89.399 84.7485 89.399H83.4858L82.9314 89.0814L55.5202 73.0127H56.0849C57.553 73.0127 58.0047 72.6293 58.1381 72.1364H58.7028L58.9902 70.3291C58.9902 70.1319 58.7644 69.8143 58.4358 69.6171C58.3007 69.5346 58.1498 69.4859 57.9944 69.4747H57.6761C57.5529 69.4747 57.4605 69.4747 57.44 69.6171L56.9678 71.4354L57.5632 71.7859V71.994C57.5016 72.3555 56.9062 72.6512 56.2286 72.6512H54.9658L54.432 72.3336L48.2208 68.7847Z" fill="url(#paint0_linear_690_34085)"/>
                    <path d="M34.4484 93.8004L29.1484 91.1762V90.6738L34.4484 93.2981V93.8004Z" fill="url(#paint1_linear_690_34085)"/>
                    <path d="M33.3229 85.981L27.8281 90.8351L32.9332 95.3607L38.428 90.4916L33.3229 85.981Z" fill="url(#paint2_linear_690_34085)"/>
                    <path opacity="0.3" d="M35.3939 87.8308L31.7969 87.542V90.6686L33.7843 91.4502L35.7718 92.2319L38.4218 90.6686L35.3939 87.8308Z" fill="#262B34"/>
                    <path d="M31.7969 59.4082V89.593C31.8077 89.7579 31.8495 89.9157 31.9182 90.0503C31.9868 90.1849 32.0797 90.2914 32.1873 90.3588C32.4769 90.5661 32.7974 90.6742 33.1224 90.6742C33.4475 90.6742 33.7679 90.5661 34.0576 90.3588C34.1641 90.2893 34.256 90.1822 34.3244 90.0481C34.3928 89.9139 34.4354 89.7571 34.448 89.593V59.4082H31.7969Z" fill="#282C3E"/>
                    <path d="M9.52324 71.9116L2.64844 75.1577L9.03567 78.1648L15.8983 75.0382L9.52324 71.9116Z" fill="url(#paint3_linear_690_34085)"/>
                    <path opacity="0.3" d="M10.6094 76.0876L10.6094 71.9116L7.95826 71.9116L7.95826 76.1522L9.25972 78.1648L10.6094 76.0876Z" fill="#262B34"/>
                    <path d="M7.94922 43.7734V73.971C7.95969 74.1344 8.00153 74.2908 8.07029 74.4234C8.13905 74.5561 8.23214 74.6599 8.33962 74.724C8.62928 74.9313 8.94972 75.0394 9.27477 75.0394C9.59982 75.0394 9.92026 74.9313 10.2099 74.724C10.3163 74.6577 10.4084 74.5532 10.4769 74.421C10.5454 74.2888 10.588 74.1336 10.6003 73.971V43.7734H7.94922Z" fill="#282C3E"/>
                    <path d="M42.4042 68.7858L5.30469 42.216V6.25391L42.4042 32.8118V68.7858Z" fill="#262B34"/>
                    <path opacity="0.3" d="M42.3933 68.7851L39.7422 66.1377V34.3926L42.3933 39.8346V68.7851Z" fill="#262B34"/>
                    <path d="M42.3984 68.7888L45.0496 68.0493V28.1431L42.3984 28.8826V68.7888Z" fill="#5E5E5E"/>
                    <path d="M6.27786 3.12695L5.30469 3.81598L44.081 31.2663L45.0542 30.5773L6.27786 3.12695Z" fill="#5E5E5E"/>
                    <path opacity="0.3" d="M37.0977 25.0112H37.4512V28.1378H39.7488L37.0977 25.0112Z" fill="#262B34"/>
                    <path opacity="0.3" d="M10.6094 6.25391H10.9629V9.3805H13.2605L10.6094 6.25391Z" fill="#262B34"/>
                    <path d="M39.7495 68.7858L0 42.2279V6.25391L39.7495 32.8118V68.7858Z" fill="#282C3E"/>
                    <path d="M37.0995 68.516V68.7862L0 43.2479V9.38086L0.19702 9.52183V43.1069L37.0995 68.516Z" fill="#1F1F1F"/>
                    <path d="M36.5996 35.2178V67.834L0.5 42.9365V10.333L36.5996 35.2178Z" fill="white" stroke="#282C3E"/>
                    <path d="M0.973171 6.25391L0 6.94293L38.7763 34.3933L39.7495 33.7166L0.973171 6.25391Z" fill="#5E5E5E"/>
                    <path d="M42.2659 18.9333C42.1568 18.8177 42.0359 18.7573 41.9131 18.7573C41.7904 18.7573 41.6694 18.8177 41.5603 18.9333L39.7422 21.0353L40.4605 21.8839L42.2659 19.7428C42.3039 19.7077 42.3361 19.6508 42.3587 19.579C42.3813 19.5072 42.3933 19.4235 42.3933 19.338C42.3933 19.2525 42.3813 19.1689 42.3587 19.0971C42.3361 19.0253 42.3039 18.9684 42.2659 18.9333Z" fill="url(#paint4_linear_690_34085)"/>
                    <path d="M39.4009 21.8892L39.2558 21.9772C38.5697 22.3485 37.9986 22.9387 37.6157 23.6726C37.2328 24.4065 37.0554 25.2506 37.1062 26.0971V27.9005C37.1103 27.9388 37.1235 27.9753 37.1443 28.0062C37.1651 28.037 37.1929 28.0613 37.2249 28.0765C37.3096 28.1199 37.4019 28.1424 37.4952 28.1424C37.5885 28.1424 37.6808 28.1199 37.7656 28.0765C37.7975 28.0613 37.8253 28.037 37.8461 28.0062C37.8669 27.9753 37.8801 27.9388 37.8843 27.9005V25.6133C37.9025 25.0892 38.0313 24.5772 38.2604 24.1188C38.4895 23.6603 38.8124 23.2682 39.2031 22.9742L39.7306 22.6223C39.7426 22.5923 39.7488 22.5598 39.7488 22.527C39.7488 22.4941 39.7426 22.4617 39.7306 22.4317C39.7255 22.3272 39.6992 22.2254 39.6535 22.1339C39.6078 22.0425 39.5439 21.9638 39.4668 21.9038C39.446 21.8938 39.4235 21.8888 39.4009 21.8892Z" fill="url(#paint5_linear_690_34085)"/>
                    <path d="M15.7559 0.797218L13.957 4.88297L13.553 3.96117L13.25 4.63384L13.957 6.25319L15.7559 1.69409C15.7981 1.62258 15.8343 1.50476 15.86 1.3546C15.8857 1.20445 15.9 1.02827 15.9011 0.847036V0C15.8958 0.170317 15.8798 0.332634 15.8544 0.471967C15.829 0.611301 15.7952 0.723159 15.7559 0.797218Z" fill="url(#paint6_linear_690_34085)"/>
                    <path d="M15.7465 0.173903C15.6372 0.0596 15.5162 0 15.3935 0C15.2707 0 15.1497 0.0596 15.0404 0.173903L13.25 2.28849L13.9561 3.1266L15.7528 1.01201C15.7964 0.983107 15.8344 0.926341 15.8611 0.850055C15.8878 0.773769 15.9018 0.681984 15.9011 0.588188C15.9004 0.494391 15.885 0.403538 15.8572 0.328967C15.8293 0.254396 15.7905 0.200041 15.7465 0.173903Z" fill="url(#paint7_linear_690_34085)"/>
                    <path d="M12.8712 3.1534L12.7288 3.24099C12.0549 3.61072 11.4942 4.19852 11.1181 4.92929C10.7421 5.66005 10.5679 6.50061 10.6177 7.34355V9.13933C10.6218 9.17749 10.6347 9.21378 10.6552 9.24453C10.6756 9.27529 10.7029 9.29943 10.7343 9.31453C10.8176 9.35776 10.9081 9.38015 10.9998 9.38015C11.0914 9.38015 11.182 9.35776 11.2653 9.31453C11.2966 9.29943 11.3239 9.27529 11.3444 9.24453C11.3648 9.21378 11.3778 9.17749 11.3819 9.13933V6.86176C11.3997 6.33989 11.5263 5.8301 11.7512 5.37357C11.9762 4.91704 12.2934 4.52659 12.677 4.23378L13.195 3.88339C13.2178 3.8593 13.2355 3.82972 13.2467 3.79685C13.2579 3.76399 13.2624 3.72869 13.2598 3.6936C13.2548 3.58957 13.2289 3.48818 13.184 3.39711C13.1392 3.30605 13.0765 3.2277 13.0008 3.168C12.9878 3.1242 12.923 3.1096 12.8712 3.1534Z" fill="url(#paint8_linear_690_34085)"/>
                    <path d="M4.49505 9.69235L2.64844 10.3162V11.1043L4.49505 10.4804C5.71381 10.0645 6.85871 9.86748 6.85871 10.6008V12.3192C6.86254 12.3482 6.8802 12.3759 6.90963 12.3992C6.93906 12.4224 6.97903 12.4402 7.0249 12.4505C7.14112 12.4879 7.27125 12.5075 7.40346 12.5075C7.53567 12.5075 7.6658 12.4879 7.78202 12.4505C7.82789 12.4402 7.86786 12.4224 7.89729 12.3992C7.92672 12.3759 7.94438 12.3482 7.94821 12.3192V10.6117C7.96667 9.42968 6.39706 9.04659 4.49505 9.69235Z" fill="url(#paint9_linear_690_34085)"/>
                    <path d="M0.297895 11.8496C0.21775 11.8089 0.148028 11.7437 0.0957377 11.6606C0.0434474 11.5776 0.0104245 11.4796 0 11.3766V11.8496C0.0156012 11.9487 0.0507433 12.0419 0.102537 12.1216C0.154331 12.2012 0.221299 12.2651 0.297895 12.3079C0.515255 12.4391 0.755504 12.5075 0.999176 12.5075C1.24285 12.5075 1.4831 12.4391 1.70046 12.3079L5.29993 10.12V9.38086L1.70046 11.8496C1.48499 11.9881 1.244 12.0606 0.999176 12.0606C0.754352 12.0606 0.513364 11.9881 0.297895 11.8496Z" fill="url(#paint10_linear_690_34085)"/>
                    <path d="M0.290125 11.4925C0.205281 11.523 0.131817 11.5795 0.0798579 11.6542C0.0278992 11.729 0 11.8182 0 11.9098C0 12.0013 0.0278992 12.0906 0.0798579 12.1653C0.131817 12.24 0.205281 12.2965 0.290125 12.327C0.505929 12.4455 0.747272 12.5075 0.992477 12.5075C1.23768 12.5075 1.47904 12.4455 1.69485 12.327L5.29993 10.2154L3.87032 9.38086L0.290125 11.4925Z" fill="url(#paint11_linear_690_34085)"/>
                    <path d="M9.27335 78.1652L2.64844 75.5423V75.0386L9.27335 77.6614V78.1652Z" fill="url(#paint12_linear_690_34085)"/>
                    <path d="M9.27344 78.1652L15.8984 75.5121V75.0386L9.27344 77.6917V78.1652Z" fill="url(#paint13_linear_690_34085)"/>
                    <path d="M32.9177 95.3839L38.4306 90.6681L38.4279 90.4735L32.814 94.9278L32.9177 95.3839Z" fill="url(#paint14_linear_690_34085)"/>
                    <defs>
                    <linearGradient id="paint0_linear_690_34085" x1="46.3729" y1="68.7847" x2="46.3729" y2="96.924" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_690_34085" x1="30.079" y1="90.5526" x2="33.8297" y2="95.5403" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#737373"/>
                    <stop offset="1" stop-color="#242424"/>
                    </linearGradient>
                    <linearGradient id="paint2_linear_690_34085" x1="33.7711" y1="85.7719" x2="34.1419" y2="84.5339" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5E5E5E"/>
                    <stop offset="1" stop-color="#262B34"/>
                    </linearGradient>
                    <linearGradient id="paint3_linear_690_34085" x1="10.0718" y1="71.7722" x2="10.2202" y2="70.8939" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5E5E5E"/>
                    <stop offset="1" stop-color="#262B34"/>
                    </linearGradient>
                    <linearGradient id="paint4_linear_690_34085" x1="41.8082" y1="23.1373" x2="44.2562" y2="25.4975" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4F4F4F"/>
                    <stop offset="1" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="paint5_linear_690_34085" x1="38.9525" y1="25.4227" x2="41.7612" y2="27.2508" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#292929"/>
                    <stop offset="1"/>
                    </linearGradient>
                    <linearGradient id="paint6_linear_690_34085" x1="13.25" y1="3.11414" x2="15.9011" y2="3.11414" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4F4F4F"/>
                    <stop offset="1" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="paint7_linear_690_34085" x1="15.2926" y1="4.32572" x2="17.7156" y2="6.67946" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4F4F4F"/>
                    <stop offset="1" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="paint8_linear_690_34085" x1="12.4309" y1="6.67196" x2="15.1993" y2="8.45801" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#292929"/>
                    <stop offset="1"/>
                    </linearGradient>
                    <linearGradient id="paint9_linear_690_34085" x1="4.51351" y1="8.98092" x2="5.13093" y2="12.85" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#292929"/>
                    <stop offset="1"/>
                    </linearGradient>
                    <linearGradient id="paint10_linear_690_34085" x1="-0.012412" y1="10.9479" x2="5.29993" y2="10.9479" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4F4F4F"/>
                    <stop offset="1" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="paint11_linear_690_34085" x1="2.47803" y1="8.49574" x2="2.33658" y2="6.65" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#4F4F4F"/>
                    <stop offset="1" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="paint12_linear_690_34085" x1="3.81159" y1="74.917" x2="7.26444" y2="80.6534" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#737373"/>
                    <stop offset="1" stop-color="#242424"/>
                    </linearGradient>
                    <linearGradient id="paint13_linear_690_34085" x1="17.2398" y1="80.2305" x2="16.7393" y2="79.4241" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#737373"/>
                    <stop offset="1" stop-color="#242424"/>
                    </linearGradient>
                    <linearGradient id="paint14_linear_690_34085" x1="40.5563" y1="95.2932" x2="39.8834" y2="94.7035" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5E5E5E"/>
                    <stop offset="1" stop-color="#242424"/>
                    </linearGradient>
                    </defs>
                    </svg>

            </div>
            <p class="text-sm font-bold text-gray-400">No Hoarding Selected</p>
        </div>

        {{-- ════════════════════════════════════════
             OOH TABLE
        ════════════════════════════════════════ --}}
        <div id="offerOohSection" class="hidden mb-5">
            <div class="flex items-center gap-2 mb-2">
                <h4 class="text-xs font-bold text-gray-700">OOH (</h4>
                <span id="offerOohCount" class="text-xs font-bold text-gray-700">0</span>
                <span class="text-xs font-bold text-gray-700">)</span>
                <span class="text-[10px] text-gray-400">— Selected Static Hoardings for the offer</span>
            </div>
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="min-w-[640px] w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold w-6">Sn↑</th>
                            <th class="px-3 py-2.5 font-semibold">Hoardings ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Rental ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Duration ↕</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Total Price ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="offerOohList" class="divide-y divide-gray-50 bg-white"></tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             DOOH TABLE
        ════════════════════════════════════════ --}}
        <div id="offerDoohSection" class="hidden mb-5">
            <div class="flex items-center gap-2 mb-2">
                <h4 class="text-xs font-bold text-gray-700">DIGITAL-DOOH (</h4>
                <span id="offerDoohCount" class="text-xs font-bold text-gray-700">0</span>
                <span class="text-xs font-bold text-gray-700">)</span>
                <span class="text-[10px] text-gray-400">— Selected Digital Screens for the offer</span>
            </div>
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="min-w-[680px] w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold w-6">Sn↑</th>
                            <th class="px-3 py-2.5 font-semibold">Hoardings ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Rental ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Slot ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Duration ↕</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Total Price ↕</th>
                            <th class="px-3 py-2.5 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="offerDoohList" class="divide-y divide-gray-50 bg-white"></tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TOTAL BAR
        ════════════════════════════════════════ --}}
        <div id="offerTotalBar" class="hidden border-t border-gray-100 pt-3 mb-1 flex items-center justify-between">
            <p class="text-xs font-bold text-gray-500">
                Total Hoardings (<span id="offerTotalCount">0</span>)
                <span class="font-normal text-gray-400 ml-1">View of all selected hoardings added to this offer</span>
            </p>
            <p class="text-base font-black text-[#2D5A43]" id="offerGrandTotal">₹0</p>
        </div>

        {{-- ════════════════════════════════════════
             ACTION BUTTONS (sticky bottom)
        ════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-auto pt-5 border-t border-gray-100">
            <button type="button" onclick="offerCancel()"
                class="w-full sm:flex-1 min-h-[44px] py-2.5 bg-[#7A9C89] border border-transparent font-bold text-white transition cursor-pointer rounded text-sm">
                Cancel
            </button>
            <button id="offer-submit-btn" type="button" onclick="offerOpenPreview()"
                disabled
                class="w-full sm:flex-1 min-h-[44px] py-2.5 bg-[#2E5B42] text-white font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition cursor-pointer rounded text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                Preview &amp; Create Offer (<span id="offer-btn-count">0</span>)
            </button>
        </div>

    </div>{{-- /flex-1 --}}
</div>



{{-- ════════════════════════════════════════════════════════════
    DATE PICKER MODAL
════════════════════════════════════════════════════════════ --}}
@include('offers::components.vendor.offer-date-modal')

{{-- ════════════════════════════════════════════════════════════
    OFFER PREVIEW MODAL (required for Preview & Create Offer)
════════════════════════════════════════════════════════════ --}}
@include('offers::components.vendor.offer-preview')

{{-- ════════════════════════════════════════════════════════════
     JAVASCRIPT — FORM STATE & RENDER
════════════════════════════════════════════════════════════ --}}
<script>
/* ──────────────────────────────────────────────
   SERVER DATA
────────────────────────────────────────────── */
const OFFER_ENQUIRY_ID    = @json($enquiry->id ?? null);
const OFFER_ENQUIRY_ITEMS = {!! $enquiryItemsJson !!};
const OFFER_STORE_URL     = "{{ route('vendor.offers.store') }}";
const OFFER_SEND_URL      = "/vendor/offers/{id}/send";
const OFFER_CSRF          = "{{ csrf_token() }}";
const OFFER_CUSTOMER_URL  = "{{ route('vendor.offers.customer-suggestions') }}";

/* ──────────────────────────────────────────────
   STATE
────────────────────────────────────────────── */
const offerItems = new Map(); // hoardingId → ItemState
let offerEditingId = null;
let offerFp        = null;
let offerHeatmap   = {};
let offerDpStart   = null;

/* ──────────────────────────────────────────────
   HELPERS
────────────────────────────────────────────── */
const offerFmt = v =>
    new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(v ?? 0);

function offerYMD(date) {
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function offerMonths(s, e) {
    if (!s || !e) return 1;
    return Math.max(1, Math.ceil((new Date(e) - new Date(s)) / 86400000 / 30 + 1/30));
}

function offerEndForMonths(startISO, n) {
    const d = new Date(startISO);
    d.setDate(d.getDate() + n * 30 - 1);
    return offerYMD(d);
}

function offerSnapEnd(startISO, rawEnd) {
    return offerEndForMonths(startISO, offerMonths(startISO, rawEnd));
}

function offerRange(s, e) {
    const fmt = d => new Date(d).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'2-digit' });
    const m = offerMonths(s, e);
    const days = Math.round((new Date(e) - new Date(s)) / 86400000) + 1;
    return { start:fmt(s), end:fmt(e), months:m, days, badge: m === 1 ? '1 Month' : `${m} Months`, full:`${fmt(s)} – ${fmt(e)}` };
}

function offerEnumDates(s, e) {
    const dates = [], cur = new Date(s), last = new Date(e);
    while (cur <= last) { dates.push(offerYMD(cur)); cur.setDate(cur.getDate()+1); }
    return dates;
}

function offerToast(msg, type = 'info') {
    if (window.Swal) {
        Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, icon:type, title:msg });
    } else {
        console.log(`[${type}] ${msg}`);
    }
}

function offerCancel() {
    if (offerItems.size > 0) {
        if (window.Swal) {
            Swal.fire({
                title: 'Cancel Offer?',
                text: 'All selected hoardings will be cleared.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2D5A43',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Yes, cancel',
                cancelButtonText: 'Keep editing'
            }).then(r => { if (r.isConfirmed) location.reload(); });
        } else {
            if (confirm('Cancel and clear all changes?')) location.reload();
        }
    } else {
        location.reload();
    }
}

/* ──────────────────────────────────────────────
   CUSTOMER SEARCH (direct mode only)
────────────────────────────────────────────── */
let offerCustomerTimer = null;

async function offerSearchCustomer(q) {
    clearTimeout(offerCustomerTimer);
    const dropdown = document.getElementById('offerCustomerDropdown');
    if (!dropdown) return;

    if (!q || q.trim().length < 2) {
        dropdown.classList.add('hidden');
        return;
    }

    offerCustomerTimer = setTimeout(async () => {
        try {
            const res = await fetch(`${OFFER_CUSTOMER_URL}?search=${encodeURIComponent(q)}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const customers = data.data ?? [];

            if (!customers.length) {
                dropdown.innerHTML = `<div class="px-4 py-3 text-xs text-gray-400 text-center">No customers found</div>`;
            } else {
                dropdown.innerHTML = customers.map(c => `
                    <button type="button" onclick="offerSelectCustomer(${c.id}, '${(c.name||'').replace(/'/g,"\\'")}', '${(c.email||'').replace(/'/g,"\\'")}', '${(c.phone||'').replace(/'/g,"\\'")}', '${(c.business_name||'').replace(/'/g,"\\'")}', '${(c.gstin||'').replace(/'/g,"\\'")}', '${(c.address||'').replace(/'/g,"\\'")}' )"
                        class="w-full text-left px-4 py-2.5 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition">
                        <p class="text-xs font-semibold text-gray-800">${c.name}</p>
                        <p class="text-[10px] text-gray-400">${c.email ?? ''} ${c.phone ? '· '+c.phone : ''}</p>
                    </button>`).join('');
            }
            dropdown.classList.remove('hidden');
        } catch(e) {
            console.error(e);
        }
    }, 300);
}

function offerSelectCustomer(id, name, email, phone, business, gstin, address) {
    document.getElementById('offerCustomerId').value    = id;
    document.getElementById('offerCustomerName').value  = name;
    document.getElementById('offerCustomerEmail').value = email;
    document.getElementById('offerCustomerPhone').value = phone;
    document.getElementById('offerCustomerBusiness').value = business;
    document.getElementById('offerCustomerGstin').value = gstin;
    document.getElementById('offerCustomerAddress').value = address;
    document.getElementById('offerCustomerInput').value = name;
    document.getElementById('offerCustomerDropdown').classList.add('hidden');
    document.getElementById('offerCustomerClearBtn')?.classList.remove('hidden');
}

function offerClearCustomer() {
    ['offerCustomerId','offerCustomerName','offerCustomerEmail','offerCustomerPhone',
     'offerCustomerBusiness','offerCustomerGstin','offerCustomerAddress'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const input = document.getElementById('offerCustomerInput');
    if (input) input.value = '';
    document.getElementById('offerCustomerClearBtn')?.classList.add('hidden');
    document.getElementById('offerCustomerDropdown')?.classList.add('hidden');
}

function offerNewCustomer() {
    offerToast('New customer creation coming soon.', 'info');
}

// Close dropdown on outside click
document.addEventListener('click', e => {
    const search = document.getElementById('offerCustomerSearch');
    if (search && !search.contains(e.target)) {
        document.getElementById('offerCustomerDropdown')?.classList.add('hidden');
    }
});

/* ──────────────────────────────────────────────
   ADD / REMOVE HOARDING
────────────────────────────────────────────── */
window.offerAddHoarding = function(hoarding) {
    if (offerItems.has(hoarding.id)) {
        offerToast('Already added.', 'info');
        return;
    }
    offerItems.set(hoarding.id, {
        enquiryItemId:   null,
        hoardingId:      hoarding.id,
        hoardingType:    (hoarding.hoarding_type ?? hoarding.type ?? 'ooh').toLowerCase(),
        title:           hoarding.title ?? hoarding.name,
        pricePerMonth:   Number(hoarding.price_per_month ?? 0),
        displayLocation: hoarding.display_location ?? hoarding.city ?? '',
        size:            hoarding.size ?? '',
        slotsPerDay:     hoarding.total_slots_per_day ?? 300,
        imageUrl:        hoarding.image_url ?? null,
        packageId:       null,
        packageType:     null,
        packageLabel:    null,
        durationMonths:  null,
        services:        null,
        startDate:       null,
        endDate:         null,
        availStatus:     'unchecked',
        conflictLabel:   null,
    });
    offerRender();
    setTimeout(() => offerOpenDateModal(hoarding.id), 150);
};

window.offerRemoveHoarding = function(hoardingId) {
    offerItems.delete(hoardingId);
    offerRender();
    // Deselect card in inventory
    const card = document.querySelector(`.offer-card[data-id="${hoardingId}"]`);
    if (card) {
        card.classList.remove('selected');
        card.querySelector('.check-badge')?.classList.add('hidden');
    }
    if (typeof offerUpdateSummary === 'function') offerUpdateSummary();
    const unselectBtn = document.getElementById('offer-unselect-btn');
    if (unselectBtn && window.offerSelectedHoardings) {
        unselectBtn.classList.toggle('hidden', window.offerSelectedHoardings.size === 0);
    }
};

/* ──────────────────────────────────────────────
   RENDER
────────────────────────────────────────────── */
function offerRender() {
    const oohBody  = document.getElementById('offerOohList');
    const doohBody = document.getElementById('offerDoohList');
    const oohSection  = document.getElementById('offerOohSection');
    const doohSection = document.getElementById('offerDoohSection');
    const emptyState  = document.getElementById('offerEmptyState');
    const totalBar    = document.getElementById('offerTotalBar');

    let oohRows = [], doohRows = [];
    let grandTotal = 0, oohIdx = 0, doohIdx = 0;

    offerItems.forEach((item) => {
        const isDooh   = item.hoardingType === 'dooh';
        const hasDates = Boolean(item.startDate && item.endDate);
        const months   = hasDates ? offerMonths(item.startDate, item.endDate) : 0;
        const total    = hasDates ? item.pricePerMonth * months : 0;
        const rng      = hasDates ? offerRange(item.startDate, item.endDate) : null;
        if (hasDates) grandTotal += total;

        const durCell = _offerDurCell(item, rng);
        const rmBtn = `<button onclick="offerRemoveHoarding(${item.hoardingId})" title="Remove"
            class="text-red-400 hover:text-red-600 transition p-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg></button>`;

        const mediaCell = `<div class="flex items-center gap-2">
            ${item.imageUrl ? `<img src="${item.imageUrl}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0" onerror="this.style.display='none'">` : `<div class="w-9 h-9 rounded bg-gray-100 flex-shrink-0 flex items-center justify-center"><svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>`}
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate max-w-[140px]">${item.title}</p>
                ${item.displayLocation ? `<p class="text-[9px] text-gray-400 flex items-center gap-0.5"><svg class="w-2.5 h-2.5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span class="truncate max-w-[120px]">${item.displayLocation}</span></p>` : ''}
                ${item.size ? `<p class="text-[9px] text-gray-400">${item.size}</p>` : ''}
            </div>
        </div>`;

        if (!isDooh) {
            oohIdx++;
            oohRows.push(`<tr class="hover:bg-gray-50 border-b border-gray-50">
                <td class="px-3 py-3 text-xs text-gray-400 font-semibold">${oohIdx}</td>
                <td class="px-3 py-3">${mediaCell}</td>
                <td class="px-3 py-3 text-xs font-semibold text-gray-700 whitespace-nowrap">${offerFmt(item.pricePerMonth)}</td>
                <td class="px-3 py-3">${durCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-right text-gray-800">${hasDates ? offerFmt(total) : '—'}</td>
                <td class="px-3 py-3">${rmBtn}</td>
            </tr>`);
        } else {
            doohIdx++;
            doohRows.push(`<tr class="hover:bg-gray-50 border-b border-gray-50">
                <td class="px-3 py-3 text-xs text-gray-400 font-semibold">${doohIdx}</td>
                <td class="px-3 py-3">${mediaCell}</td>
                <td class="px-3 py-3 text-xs font-semibold text-gray-700 whitespace-nowrap">${offerFmt(item.pricePerMonth)}<br><span class="text-[9px] font-normal text-gray-400">Min. Spend</span></td>
                <td class="px-3 py-3 text-xs text-gray-600 text-center">${item.slotsPerDay}</td>
                <td class="px-3 py-3">${durCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-right text-gray-800">${hasDates ? offerFmt(total) : '—'}</td>
                <td class="px-3 py-3">${rmBtn}</td>
            </tr>`);
        }
    });

    const hasAny = offerItems.size > 0;
    emptyState.classList.toggle('hidden', hasAny);
    oohSection.classList.toggle('hidden', oohRows.length === 0);
    doohSection.classList.toggle('hidden', doohRows.length === 0);
    totalBar.classList.toggle('hidden', !hasAny);

    oohBody.innerHTML  = oohRows.join('');
    doohBody.innerHTML = doohRows.join('');

    document.getElementById('offerOohCount').innerText  = oohIdx;
    document.getElementById('offerDoohCount').innerText = doohIdx;
    document.getElementById('offerTotalCount').innerText = offerItems.size;
    document.getElementById('offerGrandTotal').innerText = offerFmt(grandTotal);

    // Update preview button
    const btn = document.getElementById('offer-submit-btn');
    const count = document.getElementById('offer-btn-count');
    count.innerText = offerItems.size;
    btn.disabled = offerItems.size === 0;
}

function _offerDurCell(item, rng) {
    if (!item.startDate || !item.endDate) {
        return `<button onclick="offerOpenDateModal(${item.hoardingId})"
            class="text-xs font-semibold text-orange-500 hover:text-orange-700 whitespace-nowrap flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Set dates</button>`;
    }
    const isDooh = item.hoardingType === 'dooh';
    const sub = isDooh ? `${rng.days} days` : rng.badge;
    return `<button onclick="offerOpenDateModal(${item.hoardingId})" class="text-left hover:opacity-75 transition group">
        <span class="block text-xs font-semibold text-gray-700 group-hover:text-green-700">${rng.start} – ${rng.end}</span>
        <span class="flex items-center gap-1 text-[9px] text-gray-400">
            ${sub}
            <svg class="w-2.5 h-2.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </span>
    </button>`;
}

/* ──────────────────────────────────────────────
   PREVIEW MODAL
────────────────────────────────────────────── */
function offerOpenPreview() {
    // Validate customer
    const customerId = document.getElementById('offerCustomerId')?.value;
    if (!customerId) {
        offerToast('Please select a customer first.', 'warning');
        document.getElementById('offerCustomerInput')?.focus();
        return;
    }

    // Validate items
    if (offerItems.size === 0) {
        offerToast('Add at least one hoarding.', 'warning');
        return;
    }

    // Validate all have dates
    const missing = Array.from(offerItems.values()).filter(i => !i.startDate || !i.endDate);
    if (missing.length) {
        offerToast(`Set campaign dates for: ${missing[0].title}`, 'warning');
        offerOpenDateModal(missing[0].hoardingId);
        return;
    }

    // Build preview data and open modal
    offerRenderPreview();
    document.getElementById('offerPreviewModal').classList.remove('hidden');
    document.getElementById('offerPreviewModal').classList.add('flex');
}

function offerClosePreview() {
    document.getElementById('offerPreviewModal').classList.add('hidden');
    document.getElementById('offerPreviewModal').classList.remove('flex');
}

function offerRenderPreview() {
    const customerName     = document.getElementById('offerCustomerName')?.value ?? '—';
    const customerEmail    = document.getElementById('offerCustomerEmail')?.value ?? '';
    const customerPhone    = document.getElementById('offerCustomerPhone')?.value ?? '';
    const customerBusiness = document.getElementById('offerCustomerBusiness')?.value ?? '';
    const customerGstin    = document.getElementById('offerCustomerGstin')?.value ?? '';
    const customerAddress  = document.getElementById('offerCustomerAddress')?.value ?? '';

    // For enquiry mode, pull from enquiry banner
    const enquiryId = OFFER_ENQUIRY_ID;

    // Customer details block
    const custHtml = `
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Customer Details</p>
            <p class="text-xs text-gray-700">Name: <strong>${customerName}</strong></p>
            ${customerBusiness ? `<p class="text-xs text-gray-700">Business Name: ${customerBusiness}</p>` : ''}
            ${customerGstin ? `<p class="text-xs text-gray-700">GSTIN: ${customerGstin}</p>` : ''}
            ${customerPhone ? `<p class="text-xs text-gray-700">Mobile Number: +91-${customerPhone}</p>` : ''}
            ${customerEmail ? `<p class="text-xs text-gray-700">Email: ${customerEmail}</p>` : ''}
            ${customerAddress ? `<p class="text-xs text-gray-600 mt-0.5">${customerAddress}</p>` : ''}
        </div>`;
    document.getElementById('offerPreviewCustomer').innerHTML = custHtml;

    // Hoarding summary
    const oohCount  = Array.from(offerItems.values()).filter(i => i.hoardingType !== 'dooh').length;
    const doohCount = Array.from(offerItems.values()).filter(i => i.hoardingType === 'dooh').length;
    const cities    = [...new Set(Array.from(offerItems.values()).map(i => i.displayLocation?.split(',')[0]?.trim()).filter(Boolean))];
    document.getElementById('offerPreviewHoardingSummary').innerHTML = `
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Hoarding Details</p>
            <p class="text-xs text-gray-700">Total Hoardings: <strong>${offerItems.size}</strong> | OOH: <strong>${oohCount}</strong> | DOOH: <strong>${doohCount}</strong></p>
            ${cities.length ? `<p class="text-xs text-gray-500 mt-0.5">Including Cities: ${cities.slice(0,5).join(', ')}</p>` : ''}
        </div>`;

    // Valid till
    const validTill = document.getElementById('offerValidTill')?.value;
    const daysLeft = validTill
        ? Math.max(0, Math.round((new Date(validTill) - new Date()) / 86400000))
        : null;
    document.getElementById('offerPreviewValidity').innerHTML = validTill
        ? `<span class="text-xs font-bold text-gray-800">${new Date(validTill).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'2-digit'})}</span>
           <span class="text-[10px] text-gray-400 ml-1">| ${daysLeft} days left</span>`
        : '<span class="text-[10px] text-gray-400">Not set</span>';

    // OOH table
    const oohItems = Array.from(offerItems.values()).filter(i => i.hoardingType !== 'dooh');
    const doohItems = Array.from(offerItems.values()).filter(i => i.hoardingType === 'dooh');

    const renderPreviewOoh = (items) => items.map((item, idx) => {
        const rng = offerRange(item.startDate, item.endDate);
        const months = offerMonths(item.startDate, item.endDate);
        const total = item.pricePerMonth * months;
        return `<tr class="border-b border-gray-50 hover:bg-gray-50">
            <td class="px-3 py-3 text-xs text-gray-400 font-semibold">${idx+1}</td>
            <td class="px-3 py-3">
                <div class="flex items-center gap-2">
                    ${item.imageUrl ? `<img src="${item.imageUrl}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0" onerror="this.style.display='none'">` : `<div class="w-9 h-9 rounded bg-gray-100 flex-shrink-0"></div>`}
                    <div>
                        <p class="text-xs font-semibold text-gray-800 truncate max-w-[200px]">${item.title}</p>
                        ${item.displayLocation ? `<p class="text-[9px] text-gray-400 flex items-center gap-0.5"><svg class="w-2.5 h-2.5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>${item.displayLocation}</p>` : ''}
                        ${item.size ? `<p class="text-[9px] text-gray-400">${item.size}</p>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-3 py-3 text-xs font-semibold text-gray-700">${offerFmt(item.pricePerMonth)}</td>
            <td class="px-3 py-3 text-xs text-gray-600">${rng.start} – ${rng.end}<br><span class="text-[9px] text-gray-400">${rng.badge}</span></td>
            <td class="px-3 py-3 text-xs font-bold text-right text-gray-800">${offerFmt(total)}</td>
        </tr>`;
    }).join('');

    const renderPreviewDooh = (items) => items.map((item, idx) => {
        const rng = offerRange(item.startDate, item.endDate);
        const months = offerMonths(item.startDate, item.endDate);
        const total = item.pricePerMonth * months;
        return `<tr class="border-b border-gray-50 hover:bg-gray-50">
            <td class="px-3 py-3 text-xs text-gray-400 font-semibold">${idx+1}</td>
            <td class="px-3 py-3">
                <div class="flex items-center gap-2">
                    ${item.imageUrl ? `<img src="${item.imageUrl}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0" onerror="this.style.display='none'">` : `<div class="w-9 h-9 rounded bg-gray-100 flex-shrink-0"></div>`}
                    <div>
                        <p class="text-xs font-semibold text-gray-800 truncate max-w-[200px]">${item.title}</p>
                        ${item.displayLocation ? `<p class="text-[9px] text-gray-400 flex items-center gap-0.5"><svg class="w-2.5 h-2.5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>${item.displayLocation}</p>` : ''}
                        ${item.size ? `<p class="text-[9px] text-gray-400">${item.size}</p>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-3 py-3 text-xs font-semibold text-gray-700">${offerFmt(item.pricePerMonth)}<br><span class="text-[9px] font-normal text-gray-400">Min. Spend</span></td>
            <td class="px-3 py-3 text-xs text-gray-600 text-center">${item.slotsPerDay}</td>
            <td class="px-3 py-3 text-xs text-gray-600">${rng.start} – ${rng.end}<br><span class="text-[9px] text-gray-400">${rng.days} days</span></td>
            <td class="px-3 py-3 text-xs font-bold text-right text-gray-800">${offerFmt(total)}</td>
        </tr>`;
    }).join('');

    // OOH section
    const oohPreview = document.getElementById('offerPreviewOohSection');
    if (oohItems.length) {
        oohPreview.classList.remove('hidden');
        document.getElementById('offerPreviewOohCount').innerText = oohItems.length;
        document.getElementById('offerPreviewOohBody').innerHTML = renderPreviewOoh(oohItems);
    } else {
        oohPreview.classList.add('hidden');
    }

    // DOOH section
    const doohPreview = document.getElementById('offerPreviewDoohSection');
    if (doohItems.length) {
        doohPreview.classList.remove('hidden');
        document.getElementById('offerPreviewDoohCount').innerText = doohItems.length;
        document.getElementById('offerPreviewDoohBody').innerHTML = renderPreviewDooh(doohItems);
    } else {
        doohPreview.classList.add('hidden');
    }
}

/* ──────────────────────────────────────────────
   SUBMIT
────────────────────────────────────────────── */
async function offerConfirmAndSend() {
    const sendVia = [];
    if (document.getElementById('offerSendEmail')?.checked) sendVia.push('email');
    if (document.getElementById('offerSendWhatsapp')?.checked) sendVia.push('whatsapp');

    if (!sendVia.length) {
        document.getElementById('offerSendError').classList.remove('hidden');
        return;
    }
    document.getElementById('offerSendError').classList.add('hidden');

    // Show confirm dialog
    if (window.Swal) {
        const result = await Swal.fire({
            title: 'Confirm Offer',
            html: `<p style="color:#6b7280;font-size:14px;">Are you sure you want to send this offer to customer?</p>
                   <div style="background:#f9fafb;border-radius:8px;padding:12px;margin-top:12px;text-align:left;">
                       <p style="font-size:12px;font-weight:bold;color:#374151;margin-bottom:6px;">Once Confirmed</p>
                       <p style="font-size:12px;color:#6b7280;">• Offer will send to the customer</p>
                       <p style="font-size:12px;color:#6b7280;">• Customer can modify this offer</p>
                   </div>`,
            showCancelButton: true,
            confirmButtonColor: '#2D5A43',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'rounded-2xl' }
        });
        if (!result.isConfirmed) return;
    }

    await offerDoSubmit('send', sendVia);
}

async function offerDoSubmit(action, sendVia = []) {
    const confirmBtn = document.getElementById('offerConfirmSendBtn');
    if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.innerText = 'Sending…'; }

    const items = Array.from(offerItems.values()).map(item => ({
        enquiry_item_id:      item.enquiryItemId,
        hoarding_id:          item.hoardingId,
        hoarding_type:        item.hoardingType,
        package_id:           item.packageId,
        package_type:         item.packageType,
        package_label:        item.packageLabel,
        preferred_start_date: item.startDate,
        preferred_end_date:   item.endDate,
        duration_months:      item.durationMonths ?? offerMonths(item.startDate, item.endDate),
        services:             item.services,
    }));

    const payload = {
        enquiry_id:   OFFER_ENQUIRY_ID,
        customer_id:  document.getElementById('offerCustomerId')?.value || null,
        description:  document.getElementById('offerDescription')?.value?.trim() || null,
        valid_till:   document.getElementById('offerValidTill')?.value || null,
        valid_days:   30,
        send_via:     sendVia,
        items,
    };

    try {
        const storeRes = await fetch(OFFER_STORE_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': OFFER_CSRF,
            },
            body: JSON.stringify(payload),
        });
        const storeData = await storeRes.json();

        if (!storeRes.ok || !storeData.success) {
            throw new Error(storeData.message ?? 'Failed to save offer.');
        }

        if (action === 'send') {
            const sendRes = await fetch(OFFER_SEND_URL.replace('{id}', storeData.offer_id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': OFFER_CSRF },
            });
            const sendData = await sendRes.json();
            if (!sendRes.ok || !sendData.success) throw new Error(sendData.message ?? 'Offer saved but send failed.');
        }

        // Close preview, show success modal
        offerClosePreview();
        offerShowSuccess(storeData.offer_id ?? '#—');

    } catch(e) {
        offerToast(e.message, 'error');
        if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerText = 'Confirm & Send offer'; }
    }
}

function offerShowSuccess(offerId) {
    document.getElementById('offerSuccessId').innerText = `Offer ID: #${offerId}`;
    document.getElementById('offerSuccessModal').classList.remove('hidden');
    document.getElementById('offerSuccessModal').classList.add('flex');
}

function offerGoToManage() {
    window.location.href = "{{ route('vendor.offers.create') ?? '/vendor/offers' }}";
}

/* ──────────────────────────────────────────────
   INIT — pre-load enquiry items
────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (OFFER_ENQUIRY_ITEMS && OFFER_ENQUIRY_ITEMS.length) {
        OFFER_ENQUIRY_ITEMS.forEach(item => {
            if (!item.hoarding) return;
            offerItems.set(item.hoarding.id, {
                enquiryItemId:   item.id,
                hoardingId:      item.hoarding.id,
                hoardingType:    (item.hoarding_type ?? 'ooh').toLowerCase(),
                title:           item.hoarding.title,
                pricePerMonth:   Number(item.hoarding.price_per_month ?? 0),
                displayLocation: item.hoarding.display_location ?? '',
                size:            item.hoarding.size ?? '',
                slotsPerDay:     item.hoarding.total_slots_per_day ?? 300,
                imageUrl:        item.hoarding.image_url,
                packageId:       item.package_id,
                packageType:     item.package_type,
                packageLabel:    item.package_label,
                durationMonths:  item.duration_months,
                services:        item.services,
                startDate:       item.preferred_start_date,
                endDate:         item.preferred_end_date,
                availStatus:     'unchecked',
                conflictLabel:   null,
            });
        });
        offerRender();
    }
    // Set default valid-till to 30 days from now
    const d = new Date(); d.setDate(d.getDate()+30);
    const vt = document.getElementById('offerValidTill');
    if (vt && !vt.value) vt.value = offerYMD(d);
});
</script>