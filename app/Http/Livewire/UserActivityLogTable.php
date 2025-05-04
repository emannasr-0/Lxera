<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;

class UserActivityLogTable extends Component
{
    use WithPagination;

    public $search = ''; // Search input
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when search changes
    }

    /**
     * Parse a single log line into structured data.
     *
     * @param string $line
     * @return array|null
     */
    private function parseLogLine($line)
    {
        if (!str_contains($line, 'User Activity')) {
            return null;
        }

        preg_match('/\[(.*?)\] (.*?)\.INFO: User Activity: (.*)/', $line, $matches);
        if (!$matches || count($matches) < 4) {
            return null;
        }

        $rawTimestamp = $matches[1];
        $jsonContent = $matches[3];

        $fields = json_decode($jsonContent, true);
        if (!$fields) {
            return null;
        }

        $formattedTimestamp = now()->createFromFormat('Y-m-d H:i:s', $rawTimestamp)->format('d/m/Y h:i:s A');

        return [
            'timestamp' => $rawTimestamp,
            'formatted_timestamp' => $formattedTimestamp,
            'user_role' => $fields['user_role'] ?? 'N/A',
            'user_name' => $fields['user_name'] ?? 'N/A',
            'user_id' => $fields['user_id'] ?? 'N/A',
            'user_email' => $fields['email'] ?? 'N/A',
            'route' => $fields['route'] ?? 'N/A',
            'method' => $fields['method'] ?? 'N/A',
            'ip' => $fields['ip_address'] ?? 'N/A',
            'payload' => json_encode($fields['payload'] ?? []), // Ensure payload is parsed
        ];
    }



    /**
     * Get parsed and filtered logs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLogsProperty()
    {
        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            return collect([]);
        }

        // Read logs and parse lines
        $logLines = collect(File::lines($logPath))->map(function ($line) {
            return $this->parseLogLine($line);
        })->filter(); // Remove null values (invalid or non-matching lines)

        // Sort logs by timestamp (newest first)
        $sortedLogs = $logLines->sortByDesc(function ($log) {
            return $log['timestamp']; // Sort by the timestamp field
        });

        // Apply search filter
        if ($this->search) {
            $sortedLogs = $sortedLogs->filter(function ($log) {
                return str_contains(strtolower(json_encode($log)), strtolower($this->search));
            });
        }

        return $sortedLogs->values(); // Return the cleaned and sorted collection
    }


    public function render()
    {
        // Paginate manually
        $logs = $this->logs;
        $perPage = 7;
        $currentPage = $this->page;
        $total = $logs->count();
        $paginatedLogs = $logs->slice(($currentPage - 1) * $perPage, $perPage);

        return view('livewire.user-activity-log-table', [
            'logs' => $paginatedLogs,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
        ]);
    }
}
