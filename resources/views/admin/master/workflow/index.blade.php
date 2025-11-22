@extends('template.admin')

@section('title', 'Workflows')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Workflows</h1>
        <a href="{{ route('admin.workflows.create') }}" class="btn btn-primary">Create Workflow</a>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Applicable Model</th>
                    <th>Steps Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workflows as $workflow)
                    <tr>
                        <th>{{ $loop->iteration }}</th>
                        <td>{{ $workflow->name }}</td>
                        <td>{{ $workflow->applicable_model }}</td>
                        <td>{{ $workflow->steps->count() }}</td>
                        <td class="flex gap-2">
                            <a href="{{ route('admin.workflows.edit', $workflow) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.workflows.destroy', $workflow) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No workflows found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
