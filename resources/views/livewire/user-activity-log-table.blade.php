<div>
    @php
        $role = auth()->user()->role_name ;
        if($role != 'admin'){
            abort(404);
        }
    @endphp
    <div style="height:50px;"></div>
    <div class="container">
        <div  class="text-warning" wire:loading style="font-weight:bold;">
        Loading .... (Log file may be too large)
        </div>
        <div class="mb-4 ">
            <br>
            <input type="text" class="form-control" placeholder="Search logs..." wire:model.debounce.300ms="search">
        </div>
        <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Role</th>
                    <th>User Name</th>
                    <th>User ID</th>
                    <th>User Email</th>
                    <th>Route</th>
                    <th>Method</th>
                    <th>Timestamp</th>
                    <th>IP</th>
                    <th>Payload</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $index => $log)
                    <tr>
                        <td>{{ $loop->iteration + ($currentPage - 1) * $perPage }}</td>
                        <td>{{ $log['user_role'] }}</td>
                        <td>{{ $log['user_name'] }}</td>
                        <td>{{ $log['user_id'] }}</td>
                        <td>{{ $log['user_email'] }}</td>
                        <td>{{ $log['route'] }}</td>
                        <td>{{ $log['method'] }}</td>
                        <td>{{ $log['formatted_timestamp'] }}</td> 
                        <td>{{ $log['ip'] }}</td>
                        <td><pre>{{ $log['payload'] }}</pre></td>
                        
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between">
            <div>Showing {{ $logs->count() }} of {{ $total }} logs</div>
            <div>
                <button wire:click="previousPage" class="btn btn-sm btn-secondary"
                    {{ $currentPage == 1 ? 'disabled' : '' }}>Previous</button>
                <button wire:click="nextPage" class="btn btn-sm btn-secondary"
                    {{ $currentPage * $perPage >= $total ? 'disabled' : '' }}>Next</button>
            </div>
        </div>
    </div>

</div>
