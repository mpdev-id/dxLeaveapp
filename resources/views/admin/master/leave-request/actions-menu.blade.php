<li>
    <a @click="openDetailsModal(req)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd" />
        </svg>
        View Details
    </a>
</li>
<template
    x-if="req.current_status === 'Pending'">
    <li>
        <a @click="openApprovalModal(req)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
            Approve / Reject
        </a>
    </li>
</template>
<template x-if="req.current_status === 'Draft'">
    <li>
        <a @click="submitRequest(req.id)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                viewBox="0 0 20 20" fill="currentColor">
                <path
                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
            </svg>
            Submit for Approval
        </a>
    </li>
</template>
<div class="divider my-1"></div>
<li>
    <a @click="openEditModal(req)"
        :class="{'opacity-50 cursor-not-allowed': req.current_status !== 'Draft'}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
            viewBox="0 0 20 20" fill="currentColor">
            <path
                d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
            <path fill-rule="evenodd"
                d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                clip-rule="evenodd" />
        </svg>
        Edit
    </a>
</li>
<li>
    <a @click="confirmDelete(req.id)" class="text-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                clip-rule="evenodd" />
        </svg>
        Delete
    </a>
</li>
