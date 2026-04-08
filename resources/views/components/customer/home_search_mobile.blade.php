<div class="flex flex-col w-full bg-white pt-4">
    <div class="px-4 mb-4 flex justify-center">
        <form action="{{ route('search') }}" method="GET" class="relative w-full max-w-xl" id="mobile-search-form">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                name="location"
                placeholder="Search Hoardings..." 
                class="w-full py-2  pl-10 pr-4 bg-white border border-gray-300 rounded-full text-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400 shadow-sm text-sm"
                id="mobile-search-input"
                autocomplete="off"
            >
        </form>
    </div>

    <div class="flex border-b border-gray-100">
        <div class="flex w-full max-w-md mx-auto">
            <button onclick="switchTab('hoardings', this)" 
                id="tab-hoardings"
                class="tab-link flex-1 flex items-center justify-center space-x-2 pb-3 border-b-4 border-gray-600 transition-all"
                data-scroll-target="#best-hoardings-section">
                <div class="w-6 h-6 flex items-center justify-center">
                    <svg width="26" height="22" viewBox="0 0 26 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3" d="M25.0758 20.8001L23.5154 19.9112L22.6183 19.6663H21.2946L12.4195 14.6054L11.803 14.4375H3.08203L4.78825 15.4089L4.81027 16.1547L5.71017 16.6749H5.86978L5.85052 16.0171L8.15942 17.3271L8.17869 18.0756L9.08134 18.5957H9.24095L9.21894 17.938L11.5554 19.2645L11.5664 19.6663H7.58426L10.0445 21.367H10.672L11.6875 20.8001H14.2385L14.8357 21.1441L15.4549 21.3119H24.1759L23.2815 20.8001H25.0758Z" fill="#222121"/>
                        <path d="M10.045 21.3701L6.62158 19.3942V19.0859L10.045 21.0619V21.3701Z" fill="#545454"/>
                        <path d="M10.0449 21.3645L11.5255 20.5113V20.2031L10.0449 21.0562V21.3645Z" fill="#2E2E2E"/>
                        <path d="M8.10214 18.2266L6.62158 19.0824L10.045 21.0583L11.5256 20.2052L8.10214 18.2266Z" fill="#7A7A7A"/>
                        <path opacity="0.3" d="M11.5254 20.2089L10.5842 19.6641V20.2557L10.1797 20.4897H11.0411L11.5254 20.2089Z" fill="#222121"/>
                        <path d="M10.1796 20.4885L8.69629 19.6326V3.53906L10.1796 4.39493V20.4885Z" fill="#545454"/>
                        <path d="M10.1797 20.4837L10.5842 20.2498V4.15625L10.1797 4.39017V20.4837Z" fill="#2E2E2E"/>
                        <path d="M9.10083 3.30469L8.69629 3.53861L10.1796 4.39447L10.5841 4.16055L9.10083 3.30469Z" fill="#7A7A7A"/>
                        <path d="M1.93459 10.804L1.4585 10.5288V10.4297L1.93459 10.7049V10.804Z" fill="url(#paint0_linear_9076_27215)"/>
                        <path d="M1.93457 10.7993L2.85098 10.2709V10.1719L1.93457 10.7003V10.7993Z" fill="url(#paint1_linear_9076_27215)"/>
                        <path d="M2.37215 9.89844L1.4585 10.4268L1.93459 10.702L2.851 10.1736L2.37215 9.89844Z" fill="url(#paint2_linear_9076_27215)"/>
                        <path d="M2.40833 10.3851L2.34779 10.4181L2.00654 10.22L1.5965 10.4594L1.5332 10.4236L2.00654 10.1484L2.40833 10.3851Z" fill="#2E2E2E"/>
                        <path d="M2.34748 10.4169L1.93469 10.6563L1.59619 10.4582L2.00624 10.2188L2.34748 10.4169Z" fill="url(#paint3_linear_9076_27215)"/>
                        <path d="M5.13234 12.6477L4.65625 12.3725V12.2734L5.13234 12.5486V12.6477Z" fill="url(#paint4_linear_9076_27215)"/>
                        <path d="M5.13232 12.6431L6.04873 12.1147V12.0156L5.13232 12.544V12.6431Z" fill="url(#paint5_linear_9076_27215)"/>
                        <path d="M5.5699 11.7422L4.65625 12.2706L5.13234 12.5458L6.04875 12.0174L5.5699 11.7422Z" fill="url(#paint6_linear_9076_27215)"/>
                        <path d="M5.60609 12.2261L5.54554 12.2619L5.2043 12.0637L4.79426 12.3032L4.73096 12.2674L5.2043 11.9922L5.60609 12.2261Z" fill="#2E2E2E"/>
                        <path d="M5.54523 12.2685L5.13244 12.5079L4.79395 12.3097L5.20399 12.0703L5.54523 12.2685Z" fill="url(#paint7_linear_9076_27215)"/>
                        <path d="M8.33009 14.4915L7.854 14.2135V14.1172L8.33009 14.3924V14.4915Z" fill="url(#paint8_linear_9076_27215)"/>
                        <path d="M8.33008 14.4946L9.24374 13.9663V13.8672L8.33008 14.3956V14.4946Z" fill="url(#paint9_linear_9076_27215)"/>
                        <path d="M8.76766 13.5859L7.854 14.1143L8.33009 14.3895L9.24375 13.8611L8.76766 13.5859Z" fill="url(#paint10_linear_9076_27215)"/>
                        <path d="M8.80384 14.0777L8.74329 14.1134L8.40205 13.9153L7.98925 14.1547L7.92871 14.1189L8.40205 13.8438L8.80384 14.0777Z" fill="#2E2E2E"/>
                        <path d="M8.74281 14.1122L8.33001 14.3516L7.98877 14.1535L8.40157 13.9141L8.74281 14.1122Z" fill="url(#paint11_linear_9076_27215)"/>
                        <path d="M11.151 15.3247L0 8.88507V0.164062L11.151 6.60368V15.3247Z" fill="#545454"/>
                        <path d="M10.9862 14.9886V15.0574L0.118652 8.78291V0.460938L0.179196 0.49396V8.74713L10.9862 14.9886Z" fill="#1F1F1F"/>
                        <path d="M10.9857 6.73367V14.9868L0.178711 8.74536V0.492188L10.9857 6.73367Z" fill="url(#paint12_linear_9076_27215)"/>
                        <path d="M11.1514 15.3209L11.4321 15.1585V6.4375L11.1514 6.59987V15.3209Z" fill="#2E2E2E"/>
                        <path d="M0.277948 0L0 0.159615L11.151 6.59923L11.4317 6.43687L0.277948 0Z" fill="#7A7A7A"/>
                        <defs>
                        <linearGradient id="paint0_linear_9076_27215" x1="1.5328" y1="10.4297" x2="1.96486" y2="10.9195" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint1_linear_9076_27215" x1="3.04086" y1="11.2149" x2="2.93079" y2="11.091" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint2_linear_9076_27215" x1="2.24556" y1="9.84065" x2="2.27033" y2="9.71681" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint3_linear_9076_27215" x1="1.9512" y1="10.4059" x2="2.17686" y2="10.7581" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#D9D9D9"/>
                        <stop offset="1" stop-color="#B3B3B3"/>
                        </linearGradient>
                        <linearGradient id="paint4_linear_9076_27215" x1="4.73055" y1="12.2707" x2="5.16261" y2="12.7633" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint5_linear_9076_27215" x1="6.23862" y1="13.0559" x2="6.12579" y2="12.932" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint6_linear_9076_27215" x1="5.44331" y1="11.6844" x2="5.46808" y2="11.5606" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint7_linear_9076_27215" x1="5.14895" y1="12.2574" x2="5.37461" y2="12.6069" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#D9D9D9"/>
                        <stop offset="1" stop-color="#B3B3B3"/>
                        </linearGradient>
                        <linearGradient id="paint8_linear_9076_27215" x1="7.92831" y1="14.1144" x2="8.35762" y2="14.607" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint9_linear_9076_27215" x1="9.43637" y1="14.9102" x2="9.32354" y2="14.7836" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint10_linear_9076_27215" x1="8.64107" y1="13.5281" x2="8.66584" y2="13.4043" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#292929"/>
                        <stop offset="1"/>
                        </linearGradient>
                        <linearGradient id="paint11_linear_9076_27215" x1="8.34377" y1="14.1012" x2="8.57219" y2="14.4507" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#CEE2E5"/>
                        <stop offset="1" stop-color="#D9D9D9"/>
                        </linearGradient>
                        <linearGradient id="paint12_linear_9076_27215" x1="0.0851437" y1="0.519707" x2="11.1041" y2="14.9923" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#EAF3F4"/>
                        <stop offset="1" stop-color="#CEE2E5"/>
                        </linearGradient>
                        </defs>
                    </svg>

                </div>
                <span class="text-gray-900 font-semibold text-md">Best Hoardings</span>
            </button>

            <button onclick="switchTab('spots', this)" 
                id="tab-spots"
                class="tab-link flex-1 flex items-center justify-center space-x-2 pb-3 border-b-4 border-transparent transition-all"
                data-scroll-target="#top-spots-section">
                <div class="w-6 h-6 flex items-center justify-center">
                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_9076_27247)">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.96259 0.0866751C5.50544 -0.000182058 6.13923 -0.0503963 6.68616 0.0771751L6.70245 0.0812465L11.4769 1.31625C11.6254 1.3556 11.7831 1.34118 11.922 1.27553L12.0591 1.21039C12.6589 0.922675 13.3375 0.596961 13.9482 0.428675C14.4544 0.288889 15.2131 0.0907465 15.9392 0.0242465C16.4155 -0.0205392 16.7684 0.241389 16.9692 0.444961C17.3221 0.801889 17.5162 1.23075 17.6369 1.62839C17.7374 1.95682 17.7998 2.30696 17.8514 2.60282C18.2191 4.57121 18.3635 6.57483 18.2816 8.5756C17.4117 7.94734 16.3852 7.57156 15.3153 7.48973C14.2454 7.4079 13.1737 7.62319 12.2184 8.11186C11.2631 8.60053 10.4613 9.34358 9.90158 10.259C9.34183 11.1745 9.04583 12.2268 9.04623 13.2998C9.04623 14.1684 9.22809 14.9854 9.53073 15.7427L6.6848 15.0057C6.58732 14.9785 6.48442 14.9771 6.38623 15.0017C5.88952 15.1374 5.37516 15.3206 4.82552 15.516C4.58485 15.602 4.33604 15.6893 4.07909 15.778C3.97413 15.8132 3.86602 15.8512 3.75473 15.892C3.33537 16.0412 2.87259 16.2041 2.43016 16.2882C1.97552 16.3751 1.61723 16.1607 1.40552 15.9734C0.998372 15.6137 0.79073 15.155 0.665872 14.7384C0.576725 14.4172 0.507381 14.0909 0.45823 13.7612C-0.142425 10.521 -0.153918 7.1989 0.424301 3.95453L0.492158 3.57725C0.573587 3.11582 0.678087 2.53903 0.95223 2.00703C1.03417 1.84375 1.13238 1.68916 1.24537 1.5456C1.38181 1.36734 1.5567 1.22214 1.75702 1.12082C2.29987 0.861604 2.90244 0.582032 3.45887 0.428675C3.85652 0.318747 4.40209 0.176247 4.96259 0.0866751Z" fill="#D9F2E6"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M14.8716 9.17323C14.3295 9.17288 13.7926 9.27937 13.2916 9.48663C12.7906 9.6939 12.3355 9.99786 11.952 10.3811C11.5686 10.7644 11.2645 11.2195 11.0571 11.7204C10.8497 12.2213 10.743 12.7582 10.7432 13.3003C10.7432 14.8949 11.6538 16.3077 12.5142 17.2713C12.9553 17.7639 13.4072 18.167 13.7777 18.452C13.9623 18.5945 14.136 18.7126 14.2839 18.7994C14.3563 18.8438 14.4314 18.8827 14.5092 18.9162C14.6235 18.9669 14.7466 18.9946 14.8716 18.9976C15.0412 18.9976 15.1837 18.9392 15.234 18.9162C15.3109 18.8827 15.3864 18.8438 15.4606 18.7994C15.6085 18.7126 15.7809 18.5945 15.9655 18.452C16.3373 18.167 16.7892 17.7639 17.2276 17.2713C18.0907 16.3091 19 14.8963 19 13.3003C19.0004 12.7581 18.8938 12.221 18.6865 11.72C18.4791 11.219 18.1751 10.7637 17.7916 10.3803C17.4082 9.99684 16.9529 9.69275 16.4519 9.48541C15.9509 9.27806 15.4138 9.17288 14.8716 9.17323Z" fill="#2CB67D"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M14.8726 12.3125C15.3408 12.3125 15.7208 12.6925 15.7208 13.1607V13.3656C15.7208 13.5906 15.6315 13.8064 15.4724 13.9654C15.3133 14.1245 15.0976 14.2139 14.8726 14.2139C14.6477 14.2139 14.4319 14.1245 14.2728 13.9654C14.1138 13.8064 14.0244 13.5906 14.0244 13.3656V13.1607C14.0244 12.6925 14.4044 12.3125 14.8726 12.3125Z" fill="#D9F2E6"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0594 1.21431L12.2087 1.14374V8.12353C11.256 8.61283 10.4568 9.35542 9.89891 10.2696C9.34102 11.1838 9.04608 12.2341 9.04656 13.3051C9.04656 14.171 9.22978 14.9893 9.53106 15.7466L6.68513 15.0097C6.58753 14.9829 6.48464 14.982 6.38656 15.007L6.10156 15.0884V0.0078125C6.30242 0.0150506 6.4974 0.0399315 6.68649 0.0824554L6.70142 0.0865268L11.4772 1.32153C11.6253 1.36055 11.7824 1.34614 11.921 1.28081L12.0594 1.21431Z" fill="#2CB67D"/>
                        </g>
                        <defs>
                        <clipPath id="clip0_9076_27247">
                        <rect width="19" height="19" fill="white"/>
                        </clipPath>
                        </defs>
                    </svg>

                </div>
                <span class="text-gray-500 font-medium text-md">Top Spots</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setActiveTab(tabId) {
            const hoardingsBtn = document.getElementById('tab-hoardings');
            const spotsBtn = document.getElementById('tab-spots');
            
            // Reset styles
            [hoardingsBtn, spotsBtn].forEach(btn => {
                btn.classList.remove('border-gray-600', 'text-gray-900');
                btn.classList.add('border-transparent', 'text-gray-500');
                btn.querySelector('span').classList.remove('font-semibold');
                btn.querySelector('span').classList.add('font-medium');
            });

            // Set Active styles
            const activeBtn = tabId === 'hoardings' ? hoardingsBtn : spotsBtn;
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
            activeBtn.classList.add('border-gray-600', 'text-gray-900');
            activeBtn.querySelector('span').classList.add('font-semibold');
        }

        window.switchTab = function(type, element) {
            setActiveTab(type);
            const target = element.getAttribute('data-scroll-target');
            const el = document.querySelector(target);
            if (el) {
                window.scrollTo({
                    top: el.getBoundingClientRect().top + window.scrollY - 120,
                    behavior: 'smooth'
                });
            }
        };

        // Initial state
        setActiveTab('hoardings');
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('mobile-search-input');
    var form = document.getElementById('mobile-search-form');
    if (input && form) {
        // Submit on Enter
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });
        // Submit on mouse leave (blur)
        input.addEventListener('blur', function(e) {
            if (input.value.trim() !== '') {
                form.submit();
            }
        });
    }
});
</script>