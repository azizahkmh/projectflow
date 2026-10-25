<x-modal name="edit-project" title="Edit project" description="Update scope, budget and delivery." max-width="2xl">
    <form method="POST"
          x-data="{ project: null }"
          x-on:project-edit.window="project = $event.detail; $store.modals.show('edit-project')"
          :action="'/projects/' + (project?.id ?? '')"
          class="space-y-4 px-6 py-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="label">Project name</label>
            <input name="name" type="text" required :value="project?.name" class="input">
        </div>

        <div>
            <label class="label">Description</label>
            <textarea name="description" rows="2" :value="project?.description" class="input resize-none"></textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Customer</label>
                <select name="customer_id" class="input">
                    <option value="">Internal / none</option>
                    @foreach ($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" :selected="String(project?.customer_id) === '{{ $customer->id }}'">{{ $customer->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Project manager</label>
                <select name="manager_id" class="input">
                    <option value="">Unassigned</option>
                    @foreach ($managers ?? [] as $manager)
                        <option value="{{ $manager->id }}" :selected="String(project?.manager_id) === '{{ $manager->id }}'">{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label">Budget ($)</label>
                <input name="budget" type="number" min="0" step="0.01" required :value="project?.budget" class="input">
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="input">
                    @foreach (['planning' => 'Planning', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $value => $label)
                        <option value="{{ $value }}" :selected="project?.status === '{{ $value }}'">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Priority</label>
                <select name="priority" class="input">
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" :selected="project?.priority === '{{ $value }}'">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Start date</label>
                <input name="start_date" type="date" :value="project?.start_date?.substring(0,10)" class="input">
            </div>
            <div>
                <label class="label">Deadline</label>
                <input name="deadline" type="date" :value="project?.deadline?.substring(0,10)" class="input">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="$store.modals.close('edit-project')" class="btn-ghost">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="size-4" /> Save changes
            </button>
        </div>
    </form>
</x-modal>
{{-- Shared "New project" modal. Include on any page that offers project creation. --}}
<x-modal name="new-project" title="New project" description="Track scope, budget and delivery in one place." max-width="2xl">
    <form method="POST" action="{{ route('projects.store') }}" class="space-y-4 px-6 py-5">
        @csrf

        <div>
            <label for="np-name" class="label">Project name</label>
            <input id="np-name" name="name" type="text" required value="{{ old('name') }}"
                   placeholder="e.g. Website redesign" class="input @error('name') input-error @enderror">
            @error('name')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="np-description" class="label">Description <span class="font-normal text-muted">(optional)</span></label>
            <textarea id="np-description" name="description" rows="2" placeholder="What is this project about?"
                      class="input resize-none">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="np-customer" class="label">Customer</label>
                <select id="np-customer" name="customer_id" class="input">
                    <option value="">Internal / none</option>
                    @foreach ($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="np-manager" class="label">Project manager</label>
                <select id="np-manager" name="manager_id" class="input">
                    <option value="">Unassigned</option>
                    @foreach ($managers ?? [] as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="np-budget" class="label">Budget ($)</label>
                <input id="np-budget" name="budget" type="number" min="0" step="0.01" required value="{{ old('budget', 0) }}"
                       class="input @error('budget') input-error @enderror">
                @error('budget')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="np-status" class="label">Status</label>
                <select id="np-status" name="status" class="input">
                    @foreach (['planning' => 'Planning', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'planning') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="np-priority" class="label">Priority</label>
                <select id="np-priority" name="priority" class="input">
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="np-start" class="label">Start date</label>
                <input id="np-start" name="start_date" type="date" value="{{ old('start_date') }}" class="input">
            </div>
            <div>
                <label for="np-deadline" class="label">Deadline</label>
                <input id="np-deadline" name="deadline" type="date" value="{{ old('deadline') }}"
                       class="input @error('deadline') input-error @enderror">
                @error('deadline')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="$store.modals.close('new-project')" class="btn-ghost">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="size-4" /> Create project
            </button>
        </div>
    </form>
</x-modal>
