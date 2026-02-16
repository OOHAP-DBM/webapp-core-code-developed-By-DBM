@extends('layouts.admin')

@section('title', 'Vendors Management')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Vendors Management', 'route' => route('admin.vendors.index')],
    ['label' => ucfirst(str_replace('_',' ', $status)) . ' Vendors']
]" />
@endsection

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen">

    
    {{-- Tabs --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-6 text-sm font-medium border-b border-[#E5E7EB]">
            <a href="{{ route('admin.vendors.index',['status'=>'pending_approval']) }}"
               class="pb-3 {{ $status=='pending_approval' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Requested Vendors ({{ $counts->requested }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'approved']) }}"
               class="pb-3 {{ $status=='approved' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Active Vendors ({{ $counts->active }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'suspended']) }}"
               class="pb-3 {{ $status=='suspended' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Disabled Vendors ({{ $counts->disabled }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'rejected']) }}"
               class="pb-3 {{ $status=='rejected' ? 'text-red-500 border-b-2 border-red-500' : 'text-[#9CA3AF]' }}">
                Deleted Vendors ({{ $counts->deleted }})
            </a>
        </div>

        <a href="{{route('admin.vendors.create')}}" class="bg-black text-white px-4 py-2 rounded-lg text-sm">
            + Add Vendor
        </a>
    </div>

    {{-- Search + Actions --}}
    <div class="bg-white rounded-xl p-4 flex items-center gap-4 mb-4">

        <form method="GET" action="{{ route('admin.vendors.index', ['status' => $status]) }}" class="flex-1 flex items-center gap-2" id="vendor-search-form">
        <input type="hidden" name="status" value="{{ $status }}">   
        <input class="flex-1 border rounded-lg px-4 py-2 text-sm" name="search" value="{{ request('search') }}" placeholder="Search Vendor by Name, City & State..." id="vendor-search-input" autocomplete="off">
        </form>
        <script>
        (function() {
            var input = document.getElementById('vendor-search-input');
            var form = document.getElementById('vendor-search-form');
            var timeout = null;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    form.submit();
                }, 400);
            });
        })();
        </script>

        @if($status === 'pending_approval')
            <button onclick="bulkApproveVendors()"
                class="bg-[#F59E0B] text-white px-6 py-2 rounded-lg text-sm">
                Approve All
            </button>
        @elseif($status === 'approved')
            <button onclick="bulkDisableVendors()"
                class="bg-[#F59E0B] text-white px-4 py-2 rounded-lg text-sm">
                Disable Vendor
            </button>
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @elseif($status === 'suspended')
            <button onclick="bulkEnableVendors()"
                class="bg-[#008ae0] text-white px-6 py-2 rounded-lg text-sm">
                Enable
            </button>
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @elseif($status === 'rejected')
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @endif
    </div>
{{-- TABLE --}}
@if($status === 'pending_approval')
    @include('admin.vendors.tab.pending')
@elseif($status === 'approved')
    @include('admin.vendors.tab.approved')
@elseif($status === 'suspended')
    @include('admin.vendors.tab.suspended')
@elseif($status === 'rejected')
    @include('admin.vendors.tab.rejected')
@endif


</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkAll = document.getElementById('check-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    checkAll.addEventListener('change', function () {
        rowCheckboxes.forEach(cb => {
            cb.checked = checkAll.checked;
        });
    });
    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            let allChecked = true;
            rowCheckboxes.forEach(c => {
                if (!c.checked) {
                    allChecked = false;
                }
            });
            checkAll.checked = allChecked;
        });
    });
});
</script>
<script>

function getSelectedVendors(){
    let ids = [];
    document.querySelectorAll('.row-checkbox:checked').forEach(cb=>{
        ids.push(cb.value);
    });
    return ids;
}

function bulkApproveVendors(){

    let selected = getSelectedVendors();

    if(selected.length === 0){
        showToast('warning','Please select at least one vendor');
        return;
    }

    Swal.fire({
        title: 'Commission Percentage',
        input: 'number',
        inputPlaceholder: 'Enter commission (example 10)',
        confirmButtonText: 'Approve Vendors',
        showCancelButton: true,
        confirmButtonColor:'#16a34a',
        cancelButtonColor:'#ef4444',
        inputValidator: (value)=>{
            if(!value || value <= 0 || value > 100){
                return 'Enter valid commission between 1 - 100';
            }
        }
    }).then((result)=>{

        if(result.isConfirmed){
            sendBulkApprove(selected,result.value);
        }

    });
}

function sendBulkApprove(ids,commission){

    Swal.fire({
        title: 'Approving...',
        allowOutsideClick:false,
        didOpen:()=>{
            Swal.showLoading();
        }
    });

    fetch("{{ route('admin.vendors.bulk-approve') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type':'application/json',
            'Accept':'application/json'
        },
        body:JSON.stringify({
            vendor_ids:ids,
            commission_percentage:commission
        })
    })
    .then(res=>res.json())
    .then(data=>{

        Swal.close();

        if(data.success){
            showToast('success',data.message);
            setTimeout(()=>location.reload(),1200);
        }else{
            showToast('error',data.message);
        }

    });
}

/* ---------- SweetAlert Toast (Top Right) ---------- */

function showToast(icon,message){
    const Toast = Swal.mixin({
        toast:true,
        position:'top-end',
        showConfirmButton:false,
        timer:2500,
        timerProgressBar:true
    });

    Toast.fire({
        icon:icon,
        title:message
    });
}

</script>
<script>
function bulkDisableVendors(){

    let selected = getSelectedVendors();

    if(selected.length === 0){
        showToast('warning','Please select at least one vendor');
        return;
    }

    Swal.fire({
        title: 'Disable selected vendors?',
        text: "They will not be able to login or receive bookings.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Disable',
        confirmButtonColor:'#ef4444',
        cancelButtonColor:'#9ca3af'
    }).then((result)=>{

        if(result.isConfirmed){
            sendBulkDisable(selected);
        }

    });
}

function sendBulkDisable(ids){

    Swal.fire({
        title: 'Disabling...',
        allowOutsideClick:false,
        didOpen:()=>{ Swal.showLoading(); }
    });

    fetch("{{ route('admin.vendors.bulk-disable') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type':'application/json',
            'Accept':'application/json'
        },
        body:JSON.stringify({
            vendor_ids:ids
        })
    })
    .then(res=>res.json())
    .then(data=>{

        Swal.close();

        if(data.success){
            showToast('success',data.message);
            setTimeout(()=>location.reload(),1200);
        }else{
            showToast('error',data.message);
        }
    });
}
</script>
<script>
function bulkEnableVendors(){

    let selected = getSelectedVendors();

    if(selected.length === 0){
        showToast('warning','Please select at least one vendor');
        return;
    }

    Swal.fire({
        title: 'Enable selected vendors?',
        text: "They will regain login access.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Enable',
        confirmButtonColor:'#16a34a',
        cancelButtonColor:'#9ca3af'
    }).then((result)=>{

        if(result.isConfirmed){
            sendBulkEnable(selected);
        }

    });
}

function sendBulkEnable(ids){

    Swal.fire({
        title: 'Enabling...',
        allowOutsideClick:false,
        didOpen:()=>{ Swal.showLoading(); }
    });

    fetch("{{ route('admin.vendors.bulk-enable') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type':'application/json',
            'Accept':'application/json'
        },
        body:JSON.stringify({
            vendor_ids:ids
        })
    })
    .then(res=>res.json())
    .then(data=>{

        Swal.close();

        if(data.success){
            showToast('success',data.message);
            setTimeout(()=>location.reload(),1200);
        }else{
            showToast('error',data.message);
        }
    });
}
</script>
