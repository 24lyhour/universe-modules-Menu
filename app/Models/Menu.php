<?php

namespace Modules\Menu\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\HasMutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Menu\Database\Factories\MenuFactory;

class Menu extends Model
{
    use HasFactory, BelongsToOutlet, SoftDeletes, HasMutable;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'uuid',
        'tenant_type',
        'company_id',
        'tenant_id',
        'outlet_id',
        'menu_id',
        'menu_type_id',
        'product_id',
        'category_id',
        'status',
        'name',
        'description',
        'image_url',
        'schedule_mode',
        'schedule_days',
        'schedule_start_time',
        'schedule_end_time',
        'schedule_start_date',
        'schedule_end_date',
        'schedule_status',
        'is_muted',
        'muted_at',
        'muted_until',
        'muted_reason',
        'muted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'schedule_status' => 'boolean',
    ];

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    public function outlet()
    {
        return $this->belongsTo(\Modules\Outlet\Models\Outlet::class);
    }

    public function menuType()
    {
        return $this->belongsTo(MenuType::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Schedule evaluation ───────────────────────────────────────
    //
    // Returns true if the menu is currently visible per its schedule
    // configuration. Menus with schedule_status = false (or null) are
    // treated as "always available" — schedule is opt-in.

    public function isWithinSchedule(?Carbon $when = null): bool
    {
        if (!$this->schedule_status) {
            return true; // Schedule disabled → no time-based filtering.
        }

        $when ??= now();

        return match ($this->schedule_mode) {
            'always' => true,
            'daily' => $this->matchesTimeWindow($when),
            'weekly' => $this->matchesWeeklyWindow($when),
            'date_range' => $this->matchesDateRange($when),
            default => true, // Unknown / null mode → don't gate.
        };
    }

    /**
     * Same as isWithinSchedule() but returns a tuple with a hint string
     * for the UI ("Available 11:00-14:00", "Mon-Fri only", "Date range expired").
     *
     * @return array{available:bool, hint:string|null}
     */
    public function scheduleSummary(?Carbon $when = null): array
    {
        if (!$this->schedule_status) {
            return ['available' => true, 'hint' => null];
        }

        $when ??= now();
        $available = $this->isWithinSchedule($when);

        $hint = match ($this->schedule_mode) {
            'always' => null,
            'daily' => $this->scheduleStartTime() && $this->scheduleEndTime()
                ? sprintf('Available %s–%s', $this->schedule_start_time, $this->schedule_end_time)
                : null,
            'weekly' => $this->describeWeeklySchedule(),
            'date_range' => $this->describeDateRange($when),
            default => null,
        };

        return ['available' => $available, 'hint' => $hint];
    }

    protected function matchesTimeWindow(Carbon $when): bool
    {
        $start = $this->scheduleStartTime();
        $end = $this->scheduleEndTime();
        if (!$start || !$end) return true;

        $now = $when->format('H:i');
        // Same-day window. (Crossing midnight isn't supported by the
        // current schema — front-of-house should split into two entries.)
        return $now >= $start && $now <= $end;
    }

    protected function matchesWeeklyWindow(Carbon $when): bool
    {
        $days = $this->parsedScheduleDays();
        if (!empty($days) && !in_array(strtolower($when->englishDayOfWeek), $days, true)) {
            return false;
        }
        return $this->matchesTimeWindow($when);
    }

    protected function matchesDateRange(Carbon $when): bool
    {
        if ($this->schedule_start_date && $when->lt(Carbon::parse($this->schedule_start_date)->startOfDay())) {
            return false;
        }
        if ($this->schedule_end_date && $when->gt(Carbon::parse($this->schedule_end_date)->endOfDay())) {
            return false;
        }
        return $this->matchesTimeWindow($when);
    }

    /**
     * @return array<int, string>  Lowercased day names.
     */
    protected function parsedScheduleDays(): array
    {
        if (!$this->schedule_days) return [];
        $decoded = json_decode($this->schedule_days, true);
        if (is_array($decoded)) {
            return array_map('strtolower', $decoded);
        }
        // Fallback: comma-separated.
        return array_map(fn ($d) => strtolower(trim($d)), explode(',', $this->schedule_days));
    }

    protected function scheduleStartTime(): ?string
    {
        return $this->schedule_start_time ? substr($this->schedule_start_time, 0, 5) : null;
    }

    protected function scheduleEndTime(): ?string
    {
        return $this->schedule_end_time ? substr($this->schedule_end_time, 0, 5) : null;
    }

    protected function describeWeeklySchedule(): ?string
    {
        $days = $this->parsedScheduleDays();
        if (empty($days)) return null;

        $abbrev = array_map(fn ($d) => ucfirst(substr($d, 0, 3)), $days);
        $window = ($this->schedule_start_time && $this->schedule_end_time)
            ? sprintf(', %s–%s', $this->schedule_start_time, $this->schedule_end_time)
            : '';

        return implode('/', $abbrev) . $window;
    }

    protected function describeDateRange(Carbon $when): ?string
    {
        $start = $this->schedule_start_date ? Carbon::parse($this->schedule_start_date) : null;
        $end = $this->schedule_end_date ? Carbon::parse($this->schedule_end_date) : null;

        if ($start && $when->lt($start->startOfDay())) {
            return 'Starts ' . $start->isoFormat('MMM D');
        }
        if ($end && $when->gt($end->endOfDay())) {
            return 'Ended ' . $end->isoFormat('MMM D');
        }
        if ($start && $end) {
            return sprintf('Until %s', $end->isoFormat('MMM D'));
        }
        return null;
    }
}
