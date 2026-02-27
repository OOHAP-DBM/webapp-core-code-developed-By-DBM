@extends('layouts.admin')
@section('title', "Admin Hoardings")

@section('title', 'All Hoardings')
@section('page_title', 'All Hoardings')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'All Hoardings', 'route' => route('admin.my-hoardings')],
    ['label' => 'Vendor\'s Hoardings']
]" />
@endsection
@section('content')
<div class="px-6 py-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold text-gray-900">
            Admin Hoardings
        </h1>
        <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-md
                   hover:bg-teal-700 transition">
            Add Hoarding
        </button>
    </div>
    @if($hoardings->count())

        <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
            <table class="min-w-[1200px] w-full text-sm text-left">
                <thead class="bg-gray-50 text-[11px] uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3 w-12">SN</th>
                        <th class="px-4 py-3">Hoarding Title</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Progress</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($hoardings as $index => $hoarding)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">
                                {{ $hoardings->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-green-600 font-medium">
                                    {{ $hoarding->title }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $hoarding->type }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $hoarding->address ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $hoarding->status === 'active' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ ucfirst($hoarding->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-blue-600">
                                {{ ($hoarding->completion ?? 0) }}% Complete
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="text-gray-900 hover:text-gray-600 text-xl">⋮</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 px-4">
                {{ $hoardings->links() }}
            </div>
        </div>

    @else

        <div class="bg-white border border-gray-200 rounded-lg
                    flex flex-col items-center justify-center
                    min-h-[420px] text-center">

            <!-- SVG -->
            <div class="opacity-90">
                <svg width="91" height="97" viewBox="0 0 91 97" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M48.2189 68.7847H17.1939L17.1015 69.3981L21.2081 71.775H16.773L16.6806 72.3993L20.7872 74.8091H8.03632L8.72779 72.4464L2.64648 75.6196L7.94641 78.1645H9.2714L12.5535 76.6602H23.9081L47.069 90.1877H32.963L33.6509 88.8185L33.7638 87.4165L28.0557 91.1625L32.7475 94.3281H33.887L37.8704 92.0498H50.19L58.516 96.924H89.541L89.6334 96.3106L85.8964 94.12V93.9338H90.0029L90.0953 93.3204L84.069 89.7824H84.6439C86.112 89.7824 86.5535 89.399 86.6972 88.9061H87.2618L87.539 87.0988C87.539 86.9016 87.3234 86.584 86.9949 86.3868C86.8597 86.3027 86.7092 86.2505 86.5535 86.2335H86.1941C86.0607 86.2335 85.9785 86.2335 85.958 86.3759L85.4858 88.1832L86.0812 88.5337V88.7418C86.0196 89.1033 85.4242 89.399 84.7466 89.399H83.4838L82.9294 89.0814L55.5182 73.0127H56.0829C57.551 73.0127 58.0027 72.6293 58.1362 72.1364H58.7008L58.9883 70.3291C58.9883 70.1319 58.7624 69.8143 58.4339 69.6171C58.2988 69.5346 58.1478 69.4859 57.9924 69.4747H57.6742C57.551 69.4747 57.4586 69.4747 57.438 69.6171L56.9658 71.4354L57.5613 71.7859V71.994C57.4997 72.3555 56.9042 72.6512 56.2266 72.6512H54.9639L54.43 72.3336L48.2189 68.7847Z" fill="url(#paint0_linear_15_1877)"/>
                <path d="M34.4484 93.8004L29.1484 91.1762V90.6738L34.4484 93.2981V93.8004Z" fill="url(#paint1_linear_15_1877)"/>
                <path d="M33.3209 85.981L27.8262 90.8351L32.9313 95.3607L38.426 90.4916L33.3209 85.981Z" fill="url(#paint2_linear_15_1877)"/>
                <path opacity="0.3" d="M35.3939 87.8308L31.7969 87.542V90.6686L33.7843 91.4502L35.7718 92.2319L38.4218 90.6686L35.3939 87.8308Z" fill="#262B34"/>
                <path d="M31.7969 59.4082V89.593C31.8077 89.7579 31.8495 89.9157 31.9182 90.0503C31.9868 90.1849 32.0797 90.2914 32.1873 90.3588C32.4769 90.5661 32.7974 90.6742 33.1224 90.6742C33.4475 90.6742 33.7679 90.5661 34.0576 90.3588C34.1641 90.2893 34.256 90.1822 34.3244 90.0481C34.3928 89.9139 34.4354 89.7571 34.448 89.593V59.4082H31.7969Z" fill="#282C3E"/>
                <path d="M9.52128 71.9116L2.64648 75.1577L9.03372 78.1648L15.8963 75.0382L9.52128 71.9116Z" fill="url(#paint3_linear_15_1877)"/>
                <path opacity="0.3" d="M10.6094 76.0876L10.6094 71.9116L7.95826 71.9116L7.95826 76.1522L9.25972 78.1648L10.6094 76.0876Z" fill="#262B34"/>
                <path d="M7.94922 43.7734V73.971C7.95969 74.1344 8.00153 74.2908 8.07029 74.4234C8.13905 74.5561 8.23214 74.6599 8.33962 74.724C8.62928 74.9313 8.94972 75.0394 9.27477 75.0394C9.59982 75.0394 9.92026 74.9313 10.2099 74.724C10.3163 74.6577 10.4084 74.5532 10.4769 74.421C10.5454 74.2888 10.588 74.1336 10.6003 73.971V43.7734H7.94922Z" fill="#282C3E"/>
                <path d="M42.4023 68.7858L5.30273 42.216V6.25391L42.4023 32.8118V68.7858Z" fill="#262B34"/>
                <path opacity="0.3" d="M42.3953 68.7851L39.7441 66.1377V34.3926L42.3953 39.8346V68.7851Z" fill="#262B34"/>
                <path d="M42.4004 68.7888L45.0515 68.0493V28.1431L42.4004 28.8826V68.7888Z" fill="#5E5E5E"/>
                <path d="M6.27591 3.12695L5.30273 3.81598L44.079 31.2663L45.0522 30.5773L6.27591 3.12695Z" fill="#5E5E5E"/>
                <path opacity="0.3" d="M37.0977 25.0112H37.4512V28.1378H39.7488L37.0977 25.0112Z" fill="#262B34"/>
                <path opacity="0.3" d="M10.6094 6.25391H10.9629V9.3805H13.2605L10.6094 6.25391Z" fill="#262B34"/>
                <path d="M39.7495 68.7858L0 42.2279V6.25391L39.7495 32.8118V68.7858Z" fill="#282C3E"/>
                <path d="M37.0995 68.516V68.7862L0 43.2479V9.38086L0.19702 9.52183V43.1069L37.0995 68.516Z" fill="#1F1F1F"/>
                <path d="M36.5996 35.2178V67.834L0.5 42.9365V10.333L36.5996 35.2178Z" fill="white" stroke="#282C3E"/>
                <path d="M0.973171 6.25391L0 6.94293L38.7763 34.3933L39.7495 33.7166L0.973171 6.25391Z" fill="#5E5E5E"/>
                <path d="M42.2679 18.9333C42.1588 18.8177 42.0378 18.7573 41.9151 18.7573C41.7923 18.7573 41.6713 18.8177 41.5623 18.9333L39.7441 21.0353L40.4625 21.8839L42.2679 19.7428C42.3058 19.7077 42.338 19.6508 42.3606 19.579C42.3832 19.5072 42.3953 19.4235 42.3953 19.338C42.3953 19.2525 42.3832 19.1689 42.3606 19.0971C42.338 19.0253 42.3058 18.9684 42.2679 18.9333Z" fill="url(#paint4_linear_15_1877)"/>
                <path d="M39.4009 21.8892L39.2558 21.9772C38.5697 22.3485 37.9986 22.9387 37.6157 23.6726C37.2328 24.4065 37.0554 25.2506 37.1062 26.0971V27.9005C37.1103 27.9388 37.1235 27.9753 37.1443 28.0062C37.1651 28.037 37.1929 28.0613 37.2249 28.0765C37.3096 28.1199 37.4019 28.1424 37.4952 28.1424C37.5885 28.1424 37.6808 28.1199 37.7656 28.0765C37.7975 28.0613 37.8253 28.037 37.8461 28.0062C37.8669 27.9753 37.8801 27.9388 37.8843 27.9005V25.6133C37.9025 25.0892 38.0313 24.5772 38.2604 24.1188C38.4895 23.6603 38.8124 23.2682 39.2031 22.9742L39.7306 22.6223C39.7426 22.5923 39.7488 22.5598 39.7488 22.527C39.7488 22.4941 39.7426 22.4617 39.7306 22.4317C39.7255 22.3272 39.6992 22.2254 39.6535 22.1339C39.6078 22.0425 39.5439 21.9638 39.4668 21.9038C39.446 21.8938 39.4235 21.8888 39.4009 21.8892Z" fill="url(#paint5_linear_15_1877)"/>
                <path d="M15.7579 0.797218L13.9589 4.88297L13.5549 3.96117L13.252 4.63384L13.9589 6.25319L15.7579 1.69409C15.8001 1.62258 15.8362 1.50476 15.8619 1.3546C15.8877 1.20445 15.902 1.02827 15.9031 0.847036V0C15.8978 0.170317 15.8817 0.332634 15.8563 0.471967C15.831 0.611301 15.7971 0.723159 15.7579 0.797218Z" fill="url(#paint6_linear_15_1877)"/>
                <path d="M15.7484 0.173903C15.6392 0.0596 15.5182 0 15.3954 0C15.2727 0 15.1517 0.0596 15.0424 0.173903L13.252 2.28849L13.958 3.1266L15.7547 1.01201C15.7984 0.983107 15.8363 0.926341 15.863 0.850055C15.8897 0.773769 15.9037 0.681984 15.903 0.588188C15.9023 0.494391 15.8869 0.403538 15.8591 0.328967C15.8313 0.254396 15.7925 0.200041 15.7484 0.173903Z" fill="url(#paint7_linear_15_1877)"/>
                <path d="M12.8712 3.1534L12.7288 3.24099C12.0549 3.61072 11.4942 4.19852 11.1181 4.92929C10.7421 5.66005 10.5679 6.50061 10.6177 7.34355V9.13933C10.6218 9.17749 10.6347 9.21378 10.6552 9.24453C10.6756 9.27529 10.7029 9.29943 10.7343 9.31453C10.8176 9.35776 10.9081 9.38015 10.9998 9.38015C11.0914 9.38015 11.182 9.35776 11.2653 9.31453C11.2966 9.29943 11.3239 9.27529 11.3444 9.24453C11.3648 9.21378 11.3778 9.17749 11.3819 9.13933V6.86176C11.3997 6.33989 11.5263 5.8301 11.7512 5.37357C11.9762 4.91704 12.2934 4.52659 12.677 4.23378L13.195 3.88339C13.2178 3.8593 13.2355 3.82972 13.2467 3.79685C13.2579 3.76399 13.2624 3.72869 13.2598 3.6936C13.2548 3.58957 13.2289 3.48818 13.184 3.39711C13.1392 3.30605 13.0765 3.2277 13.0008 3.168C12.9878 3.1242 12.923 3.1096 12.8712 3.1534Z" fill="url(#paint8_linear_15_1877)"/>
                <path d="M4.4931 9.69235L2.64648 10.3162V11.1043L4.4931 10.4804C5.71186 10.0645 6.85676 9.86748 6.85676 10.6008V12.3192C6.86059 12.3482 6.87825 12.3759 6.90768 12.3992C6.9371 12.4224 6.97708 12.4402 7.02295 12.4505C7.13917 12.4879 7.26929 12.5075 7.40151 12.5075C7.53372 12.5075 7.66384 12.4879 7.78007 12.4505C7.82593 12.4402 7.86591 12.4224 7.89534 12.3992C7.92477 12.3759 7.94242 12.3482 7.94625 12.3192V10.6117C7.96472 9.42968 6.39511 9.04659 4.4931 9.69235Z" fill="url(#paint9_linear_15_1877)"/>
                <path d="M0.297895 11.8496C0.21775 11.8089 0.148028 11.7437 0.0957377 11.6606C0.0434474 11.5776 0.0104245 11.4796 0 11.3766V11.8496C0.0156012 11.9487 0.0507433 12.0419 0.102537 12.1216C0.154331 12.2012 0.221299 12.2651 0.297895 12.3079C0.515255 12.4391 0.755504 12.5075 0.999176 12.5075C1.24285 12.5075 1.4831 12.4391 1.70046 12.3079L5.29993 10.12V9.38086L1.70046 11.8496C1.48499 11.9881 1.244 12.0606 0.999176 12.0606C0.754352 12.0606 0.513364 11.9881 0.297895 11.8496Z" fill="url(#paint10_linear_15_1877)"/>
                <path d="M0.290125 11.4925C0.205281 11.523 0.131817 11.5795 0.0798579 11.6542C0.0278992 11.729 0 11.8182 0 11.9098C0 12.0013 0.0278992 12.0906 0.0798579 12.1653C0.131817 12.24 0.205281 12.2965 0.290125 12.327C0.505929 12.4455 0.747272 12.5075 0.992477 12.5075C1.23768 12.5075 1.47904 12.4455 1.69485 12.327L5.29993 10.2154L3.87032 9.38086L0.290125 11.4925Z" fill="url(#paint11_linear_15_1877)"/>
                <path d="M9.2714 78.1652L2.64648 75.5423V75.0386L9.2714 77.6614V78.1652Z" fill="url(#paint12_linear_15_1877)"/>
                <path d="M9.27148 78.1652L15.8964 75.5121V75.0386L9.27148 77.6917V78.1652Z" fill="url(#paint13_linear_15_1877)"/>
                <path d="M32.9177 95.3839L38.4306 90.6681L38.4279 90.4735L32.814 94.9278L32.9177 95.3839Z" fill="url(#paint14_linear_15_1877)"/>
                <defs>
                <linearGradient id="paint0_linear_15_1877" x1="46.3709" y1="68.7847" x2="46.3709" y2="96.924" gradientUnits="userSpaceOnUse">
                <stop/>
                <stop offset="1" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="paint1_linear_15_1877" x1="30.079" y1="90.5526" x2="33.8297" y2="95.5403" gradientUnits="userSpaceOnUse">
                <stop stop-color="#737373"/>
                <stop offset="1" stop-color="#242424"/>
                </linearGradient>
                <linearGradient id="paint2_linear_15_1877" x1="33.7691" y1="85.7719" x2="34.14" y2="84.5339" gradientUnits="userSpaceOnUse">
                <stop stop-color="#5E5E5E"/>
                <stop offset="1" stop-color="#262B34"/>
                </linearGradient>
                <linearGradient id="paint3_linear_15_1877" x1="10.0698" y1="71.7722" x2="10.2182" y2="70.8939" gradientUnits="userSpaceOnUse">
                <stop stop-color="#5E5E5E"/>
                <stop offset="1" stop-color="#262B34"/>
                </linearGradient>
                <linearGradient id="paint4_linear_15_1877" x1="41.8102" y1="23.1373" x2="44.2582" y2="25.4975" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F4F4F"/>
                <stop offset="1" stop-color="#0F0F0F"/>
                </linearGradient>
                <linearGradient id="paint5_linear_15_1877" x1="38.9525" y1="25.4227" x2="41.7612" y2="27.2508" gradientUnits="userSpaceOnUse">
                <stop stop-color="#292929"/>
                <stop offset="1"/>
                </linearGradient>
                <linearGradient id="paint6_linear_15_1877" x1="13.252" y1="3.11414" x2="15.9031" y2="3.11414" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F4F4F"/>
                <stop offset="1" stop-color="#0F0F0F"/>
                </linearGradient>
                <linearGradient id="paint7_linear_15_1877" x1="15.2945" y1="4.32572" x2="17.7176" y2="6.67946" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F4F4F"/>
                <stop offset="1" stop-color="#0F0F0F"/>
                </linearGradient>
                <linearGradient id="paint8_linear_15_1877" x1="12.4309" y1="6.67196" x2="15.1993" y2="8.45801" gradientUnits="userSpaceOnUse">
                <stop stop-color="#292929"/>
                <stop offset="1"/>
                </linearGradient>
                <linearGradient id="paint9_linear_15_1877" x1="4.51156" y1="8.98092" x2="5.12898" y2="12.85" gradientUnits="userSpaceOnUse">
                <stop stop-color="#292929"/>
                <stop offset="1"/>
                </linearGradient>
                <linearGradient id="paint10_linear_15_1877" x1="-0.012412" y1="10.9479" x2="5.29993" y2="10.9479" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F4F4F"/>
                <stop offset="1" stop-color="#0F0F0F"/>
                </linearGradient>
                <linearGradient id="paint11_linear_15_1877" x1="2.47803" y1="8.49574" x2="2.33658" y2="6.65" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F4F4F"/>
                <stop offset="1" stop-color="#0F0F0F"/>
                </linearGradient>
                <linearGradient id="paint12_linear_15_1877" x1="3.80964" y1="74.917" x2="7.26248" y2="80.6534" gradientUnits="userSpaceOnUse">
                <stop stop-color="#737373"/>
                <stop offset="1" stop-color="#242424"/>
                </linearGradient>
                <linearGradient id="paint13_linear_15_1877" x1="17.2379" y1="80.2305" x2="16.7373" y2="79.4241" gradientUnits="userSpaceOnUse">
                <stop stop-color="#737373"/>
                <stop offset="1" stop-color="#242424"/>
                </linearGradient>
                <linearGradient id="paint14_linear_15_1877" x1="40.5563" y1="95.2932" x2="39.8834" y2="94.7035" gradientUnits="userSpaceOnUse">
                <stop stop-color="#5E5E5E"/>
                <stop offset="1" stop-color="#242424"/>
                </linearGradient>
                </defs>
                </svg>

            </div>

            <!-- Text -->
            <h3 class="mt-6 text-lg font-semibold text-gray-700">
                No Hoardings Found
            </h3>
            <button
                class="mt-4 px-5 py-2 text-sm font-medium text-white
                       bg-teal-600 rounded-md hover:bg-teal-700 transition">
                Add Hoarding
            </button>
        </div>

    @endif

</div>
@endsection
