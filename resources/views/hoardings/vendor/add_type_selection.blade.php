{{-- 
    Add Hoarding Type Selection (Vendor)
    Architectural rules strictly enforced:
    - Only OOH handled here
    - DOOH delegated to DOOH module
    - Pixel-perfect Figma match
--}}
@extends('layouts.vendor')
@section('title', 'Hoardings-Types')

@section('content')
<div class="container add-hoarding-type-selection">
    <div class="header">
        <div style="display: flex; flex-direction: column; align-items: center;">
            <svg width="110" height="102" viewBox="0 0 110 102" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M57.5709 68.7849H26.546L26.4536 69.3983L30.5601 71.7752H26.1251L26.0327 72.3995L30.1392 74.8093H17.3884L18.0798 72.4467L11.9985 75.6198L17.2985 78.1647H18.6234L21.9056 76.6604H33.2602L56.4211 90.1879H42.3151L43.003 88.8187L43.1159 87.4167L37.4078 91.1628L42.0995 94.3283H43.2391L47.2224 92.05H59.5421L67.8681 96.9243H98.893L98.9854 96.3109L95.2485 94.1202V93.934H99.355L99.4474 93.3206L93.421 89.7827H93.996C95.4641 89.7827 95.9055 89.3993 96.0492 88.9064H96.6139L96.8911 87.099C96.8911 86.9019 96.6755 86.5842 96.347 86.3871C96.2117 86.303 96.0612 86.2507 95.9055 86.2337H95.5462C95.4127 86.2337 95.3306 86.2337 95.3101 86.3761L94.8378 88.1834L95.4333 88.534V88.7421C95.3717 89.1035 94.7762 89.3993 94.0986 89.3993H92.8359L92.2815 89.0816L64.8703 73.0129H65.435C66.9031 73.0129 67.3548 72.6296 67.4882 72.1367H68.0529L68.3403 70.3293C68.3403 70.1322 68.1145 69.8145 67.7859 69.6174C67.6508 69.5348 67.4999 69.4861 67.3445 69.475H67.0262C66.903 69.475 66.8106 69.475 66.7901 69.6174L66.3179 71.4356L66.9133 71.7861V71.9943C66.8517 72.3557 66.2563 72.6515 65.5787 72.6515H64.3159L63.7821 72.3338L57.5709 68.7849Z" fill="url(#paint0_linear_9_37631)"/>
            <path d="M43.8004 93.8005L38.5005 91.1763V90.674L43.8004 93.2982V93.8005Z" fill="url(#paint1_linear_9_37631)"/>
            <path d="M42.673 85.9808L37.1782 90.835L42.2833 95.3606L47.7781 90.4915L42.673 85.9808Z" fill="url(#paint2_linear_9_37631)"/>
            <path opacity="0.3" d="M44.746 87.8309L41.1489 87.5421V90.6687L43.1364 91.4504L45.1239 92.232L47.7738 90.6687L44.746 87.8309Z" fill="#262B34"/>
            <path d="M41.1489 59.4084V89.5932C41.1597 89.7581 41.2016 89.916 41.2702 90.0506C41.3388 90.1852 41.4317 90.2916 41.5393 90.359C41.829 90.5663 42.1494 90.6744 42.4745 90.6744C42.7995 90.6744 43.12 90.5663 43.4096 90.359C43.5162 90.2895 43.6081 90.1825 43.6765 90.0483C43.7449 89.9141 43.7875 89.7574 43.8 89.5932V59.4084H41.1489Z" fill="#282C3E"/>
            <path d="M18.8733 71.9119L11.9985 75.1579L18.3858 78.1651L25.2484 75.0385L18.8733 71.9119Z" fill="url(#paint3_linear_9_37631)"/>
            <path opacity="0.3" d="M19.9614 76.0878L19.9614 71.9119L17.3103 71.9119L17.3103 76.1524L18.6118 78.1651L19.9614 76.0878Z" fill="#262B34"/>
            <path d="M17.3013 43.7732V73.9707C17.3117 74.1342 17.3536 74.2906 17.4223 74.4232C17.4911 74.5558 17.5842 74.6597 17.6917 74.7237C17.9813 74.9311 18.3018 75.0392 18.6268 75.0392C18.9519 75.0392 19.2723 74.9311 19.562 74.7237C19.6683 74.6574 19.7604 74.553 19.8289 74.4208C19.8975 74.2886 19.9401 74.1334 19.9524 73.9707V43.7732H17.3013Z" fill="#282C3E"/>
            <path d="M51.7543 68.7858L14.6548 42.216V6.25391L51.7543 32.8118V68.7858Z" fill="#262B34"/>
            <path opacity="0.3" d="M51.7473 68.785L49.0962 66.1375V34.3925L51.7473 39.8345V68.785Z" fill="#262B34"/>
            <path d="M51.7524 68.7887L54.4036 68.0492V28.1429L51.7524 28.8824V68.7887Z" fill="#5E5E5E"/>
            <path d="M15.628 3.12695L14.6548 3.81598L53.4311 31.2663L54.4043 30.5773L15.628 3.12695Z" fill="#5E5E5E"/>
            <path opacity="0.3" d="M46.4497 25.0111H46.8032V28.1377H49.1008L46.4497 25.0111Z" fill="#262B34"/>
            <path opacity="0.3" d="M19.9614 6.25391H20.3149V9.3805H22.6125L19.9614 6.25391Z" fill="#262B34"/>
            <path d="M49.1015 68.7858L9.35205 42.2279V6.25391L49.1015 32.8118V68.7858Z" fill="#282C3E"/>
            <path d="M46.4516 68.516V68.7862L9.35205 43.2479V9.38086L9.54907 9.52183V43.1069L46.4516 68.516Z" fill="#1F1F1F"/>
            <path d="M45.9517 35.2178V67.834L9.85205 42.9365V10.333L45.9517 35.2178Z" fill="white" stroke="#282C3E"/>
            <path d="M10.3252 6.25391L9.35205 6.94293L48.1284 34.3933L49.1015 33.7166L10.3252 6.25391Z" fill="#5E5E5E"/>
            <path d="M51.6199 18.9332C51.5109 18.8175 51.3899 18.7572 51.2671 18.7572C51.1444 18.7572 51.0234 18.8175 50.9143 18.9332L49.0962 21.0352L49.8145 21.8838L51.6199 19.7426C51.6579 19.7075 51.6901 19.6507 51.7127 19.5789C51.7353 19.5071 51.7473 19.4234 51.7473 19.3379C51.7473 19.2524 51.7353 19.1688 51.7127 19.097C51.6901 19.0252 51.6579 18.9683 51.6199 18.9332Z" fill="url(#paint4_linear_9_37631)"/>
            <path d="M48.7529 21.8891L48.6079 21.977C47.9217 22.3483 47.3507 22.9386 46.9678 23.6725C46.5849 24.4064 46.4075 25.2505 46.4582 26.097V27.9004C46.4624 27.9387 46.4755 27.9752 46.4964 28.006C46.5172 28.0369 46.545 28.0612 46.5769 28.0763C46.6617 28.1197 46.7539 28.1422 46.8472 28.1422C46.9406 28.1422 47.0328 28.1197 47.1176 28.0763C47.1495 28.0612 47.1773 28.0369 47.1982 28.006C47.219 27.9752 47.2322 27.9387 47.2363 27.9004V25.6132C47.2545 25.0891 47.3834 24.5771 47.6125 24.1187C47.8416 23.6602 48.1645 23.2681 48.5551 22.974L49.0826 22.6222C49.0946 22.5921 49.1008 22.5597 49.1008 22.5268C49.1008 22.494 49.0946 22.4616 49.0826 22.4316C49.0776 22.3271 49.0512 22.2253 49.0055 22.1338C48.9598 22.0424 48.896 21.9637 48.8189 21.9037C48.7981 21.8936 48.7756 21.8886 48.7529 21.8891Z" fill="url(#paint5_linear_9_37631)"/>
            <path d="M25.1099 0.797218L23.311 4.88297L22.907 3.96117L22.604 4.63384L23.311 6.25319L25.1099 1.69409C25.1521 1.62258 25.1883 1.50476 25.214 1.3546C25.2397 1.20445 25.254 1.02827 25.2551 0.847036V0C25.2498 0.170317 25.2338 0.332634 25.2084 0.471967C25.183 0.611301 25.1492 0.723159 25.1099 0.797218Z" fill="url(#paint6_linear_9_37631)"/>
            <path d="M25.1005 0.173903C24.9912 0.0596 24.8702 0 24.7475 0C24.6247 0 24.5037 0.0596 24.3944 0.173903L22.604 2.28849L23.3101 3.1266L25.1068 1.01201C25.1504 0.983107 25.1884 0.926341 25.2151 0.850055C25.2418 0.773769 25.2558 0.681984 25.2551 0.588188C25.2544 0.494391 25.239 0.403538 25.2112 0.328967C25.1833 0.254396 25.1445 0.200041 25.1005 0.173903Z" fill="url(#paint7_linear_9_37631)"/>
            <path d="M22.2233 3.1534L22.0808 3.24099C21.407 3.61072 20.8462 4.19852 20.4702 4.92929C20.0942 5.66005 19.9199 6.50061 19.9698 7.34355V9.13933C19.9738 9.17749 19.9868 9.21378 20.0072 9.24453C20.0277 9.27529 20.055 9.29943 20.0863 9.31453C20.1696 9.35776 20.2602 9.38015 20.3518 9.38015C20.4435 9.38015 20.5341 9.35776 20.6173 9.31453C20.6487 9.29943 20.676 9.27529 20.6964 9.24453C20.7169 9.21378 20.7298 9.17749 20.7339 9.13933V6.86176C20.7518 6.33989 20.8783 5.8301 21.1033 5.37357C21.3283 4.91704 21.6454 4.52659 22.029 4.23378L22.5471 3.88339C22.5699 3.8593 22.5875 3.82972 22.5987 3.79685C22.6099 3.76399 22.6144 3.72869 22.6118 3.6936C22.6069 3.58957 22.581 3.48818 22.5361 3.39711C22.4912 3.30605 22.4285 3.2277 22.3528 3.168C22.3399 3.1242 22.2751 3.1096 22.2233 3.1534Z" fill="url(#paint8_linear_9_37631)"/>
            <path d="M13.8451 9.69235L11.9985 10.3162V11.1043L13.8451 10.4804C15.0639 10.0645 16.2088 9.86748 16.2088 10.6008V12.3192C16.2126 12.3482 16.2303 12.3759 16.2597 12.3992C16.2892 12.4224 16.3291 12.4402 16.375 12.4505C16.4912 12.4879 16.6213 12.5075 16.7536 12.5075C16.8858 12.5075 17.0159 12.4879 17.1321 12.4505C17.178 12.4402 17.218 12.4224 17.2474 12.3992C17.2768 12.3759 17.2945 12.3482 17.2983 12.3192V10.6117C17.3168 9.42968 15.7472 9.04659 13.8451 9.69235Z" fill="url(#paint9_linear_9_37631)"/>
            <path d="M9.64995 11.8496C9.5698 11.8089 9.50008 11.7437 9.44779 11.6606C9.3955 11.5776 9.36248 11.4796 9.35205 11.3766V11.8496C9.36765 11.9487 9.40279 12.0419 9.45459 12.1216C9.50638 12.2012 9.57335 12.2651 9.64995 12.3079C9.86731 12.4391 10.1076 12.5075 10.3512 12.5075C10.5949 12.5075 10.8351 12.4391 11.0525 12.3079L14.652 10.12V9.38086L11.0525 11.8496C10.837 11.9881 10.5961 12.0606 10.3512 12.0606C10.1064 12.0606 9.86542 11.9881 9.64995 11.8496Z" fill="url(#paint10_linear_9_37631)"/>
            <path d="M9.64218 11.4925C9.55733 11.523 9.48387 11.5795 9.43191 11.6542C9.37995 11.729 9.35205 11.8182 9.35205 11.9098C9.35205 12.0013 9.37995 12.0906 9.43191 12.1653C9.48387 12.24 9.55733 12.2965 9.64218 12.327C9.85798 12.4455 10.0993 12.5075 10.3445 12.5075C10.5897 12.5075 10.8311 12.4455 11.0469 12.327L14.652 10.2154L13.2224 9.38086L9.64218 11.4925Z" fill="url(#paint11_linear_9_37631)"/>
            <path d="M18.6234 78.1654L11.9985 75.5425V75.0388L18.6234 77.6617V78.1654Z" fill="url(#paint12_linear_9_37631)"/>
            <path d="M18.6235 78.1654L25.2484 75.5123V75.0388L18.6235 77.6919V78.1654Z" fill="url(#paint13_linear_9_37631)"/>
            <path d="M42.2698 95.384L47.7826 90.6682L47.78 90.4736L42.166 94.9279L42.2698 95.384Z" fill="url(#paint14_linear_9_37631)"/>
            <defs>
            <linearGradient id="paint0_linear_9_37631" x1="55.723" y1="68.7849" x2="55.723" y2="96.9243" gradientUnits="userSpaceOnUse">
            <stop/>
            <stop offset="1" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="paint1_linear_9_37631" x1="39.431" y1="90.5527" x2="43.1818" y2="95.5404" gradientUnits="userSpaceOnUse">
            <stop stop-color="#737373"/>
            <stop offset="1" stop-color="#242424"/>
            </linearGradient>
            <linearGradient id="paint2_linear_9_37631" x1="43.1212" y1="85.7717" x2="43.492" y2="84.5338" gradientUnits="userSpaceOnUse">
            <stop stop-color="#5E5E5E"/>
            <stop offset="1" stop-color="#262B34"/>
            </linearGradient>
            <linearGradient id="paint3_linear_9_37631" x1="19.4219" y1="71.7725" x2="19.5703" y2="70.8942" gradientUnits="userSpaceOnUse">
            <stop stop-color="#5E5E5E"/>
            <stop offset="1" stop-color="#262B34"/>
            </linearGradient>
            <linearGradient id="paint4_linear_9_37631" x1="51.1622" y1="23.1372" x2="53.6102" y2="25.4974" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4F4F4F"/>
            <stop offset="1" stop-color="#0F0F0F"/>
            </linearGradient>
            <linearGradient id="paint5_linear_9_37631" x1="48.3045" y1="25.4226" x2="51.1132" y2="27.2507" gradientUnits="userSpaceOnUse">
            <stop stop-color="#292929"/>
            <stop offset="1"/>
            </linearGradient>
            <linearGradient id="paint6_linear_9_37631" x1="22.604" y1="3.11414" x2="25.2551" y2="3.11414" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4F4F4F"/>
            <stop offset="1" stop-color="#0F0F0F"/>
            </linearGradient>
            <linearGradient id="paint7_linear_9_37631" x1="24.6466" y1="4.32572" x2="27.0696" y2="6.67946" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4F4F4F"/>
            <stop offset="1" stop-color="#0F0F0F"/>
            </linearGradient>
            <linearGradient id="paint8_linear_9_37631" x1="21.7829" y1="6.67196" x2="24.5514" y2="8.45801" gradientUnits="userSpaceOnUse">
            <stop stop-color="#292929"/>
            <stop offset="1"/>
            </linearGradient>
            <linearGradient id="paint9_linear_9_37631" x1="13.8636" y1="8.98092" x2="14.481" y2="12.85" gradientUnits="userSpaceOnUse">
            <stop stop-color="#292929"/>
            <stop offset="1"/>
            </linearGradient>
            <linearGradient id="paint10_linear_9_37631" x1="9.33964" y1="10.9479" x2="14.652" y2="10.9479" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4F4F4F"/>
            <stop offset="1" stop-color="#0F0F0F"/>
            </linearGradient>
            <linearGradient id="paint11_linear_9_37631" x1="11.8301" y1="8.49574" x2="11.6886" y2="6.65" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4F4F4F"/>
            <stop offset="1" stop-color="#0F0F0F"/>
            </linearGradient>
            <linearGradient id="paint12_linear_9_37631" x1="13.1617" y1="74.9172" x2="16.6145" y2="80.6536" gradientUnits="userSpaceOnUse">
            <stop stop-color="#737373"/>
            <stop offset="1" stop-color="#242424"/>
            </linearGradient>
            <linearGradient id="paint13_linear_9_37631" x1="26.5899" y1="80.2308" x2="26.0894" y2="79.4244" gradientUnits="userSpaceOnUse">
            <stop stop-color="#737373"/>
            <stop offset="1" stop-color="#242424"/>
            </linearGradient>
            <linearGradient id="paint14_linear_9_37631" x1="49.9083" y1="95.2933" x2="49.2355" y2="94.7036" gradientUnits="userSpaceOnUse">
            <stop stop-color="#5E5E5E"/>
            <stop offset="1" stop-color="#242424"/>
            </linearGradient>
            </defs>
            </svg>
        </div>
        <h2>Start Listing Your Hoarding</h2>
        <p>Select the hoarding type which you want to list</p>
    </div>

    <form method="POST" action="{{ route('vendor.hoardings.select-type') }}">
        @csrf

        <div class="card-row">
            <label class="card-option">
                <input type="radio" name="hoarding_type" value="OOH" required>
                <div class="card">
                    <span class="title">OOH Hoarding</span><br>
                    <span class="sub">(Out-of-Home)</span>
                </div>
            </label>

            <label class="card-option">
                <input type="radio" name="hoarding_type" value="DOOH" required>
                <div class="card">
                    <span class="title">DOOH Hoarding</span><br>
                    <span class="sub">(Digital Out-of-Home)</span>
                </div>
            </label>
        </div>

        <button type="submit" class="continue-btn">
            Continue
        </button>
    </form>
</div>

<style>
/* Layout */
.add-hoarding-type-selection {
    max-width: 480px;
    margin: 0 auto;
    padding-top: 80px;
}

.header {
    text-align: center;
}

.header img {
    width: 120px;
    margin-bottom: 32px;
}

.header h2 {
    font-weight: 600;
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.header p {
    color: var(--gray-text);
    font-size: 1rem;
    margin-bottom: 32px;
}

/* Cards */
.card-row {
    display: flex;
    gap: 24px;
    justify-content: center;
    margin-bottom: 32px;
}

.card-option {
    flex: 1;
    cursor: pointer;
}

.card-option input {
    display: none;
}

.card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px 0;
    text-align: center;
    background: var(--white);
    transition: box-shadow .2s, border-color .2s;
}

.card .title {
    font-weight: 500;
    font-size: 1.1rem;
}

.card .sub {
    color: var(--gray-text);
    font-size: .95rem;
}

/* Hover */
.card-option .card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}

/* Selected */
.card-option input:checked + .card {
    border-color: var(--btn-color);
    box-shadow: 0 0 0 2px rgba(34,197,94,.25);
}

/* Continue Button */
.continue-btn {
    width: 100%;
    height: 48px;
    font-size: 1.1rem;
    font-weight: 500;
    background: var(--gray-bg);
    color: var(--gray-text);
    border: none;
    border-radius: 8px;
    cursor: not-allowed;
    transition: background .2s, color .2s;
}

/* Enable when selected */
form:has(input[type="radio"]:checked) .continue-btn {
    background: var(--btn-color);
    color: var(--white);
    cursor: pointer;
}

/* Hover enabled */
form:has(input[type="radio"]:checked) .continue-btn:hover {
    background: var(--btn-color-dark);
}
</style>
@endsection
