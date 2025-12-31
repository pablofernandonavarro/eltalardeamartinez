<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <flux:heading size="xl">Mi Hogar</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Administrá los residentes de la unidad y la información básica.</p>
        </div>
    </div>

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if($unitUsers->isEmpty())
        <flux:callout color="blue">
            No tenés una unidad funcional asignada. Contactá al administrador.
        </flux:callout>
    @else
        <div class="mb-6">
            <flux:field>
                <flux:label>Unidad Funcional</flux:label>
                <flux:select wire:model.live="unitId">
                @foreach($unitUsers as $uu)
                    <option value="{{ $uu->unit_id }}">
                        {{ $uu->unit->full_identifier }} ({{ $uu->unit->building->complex->name }})
                        @if($uu->is_responsible)
                            - Responsable
                        @endif
                    </option>
                @endforeach
                </flux:select>
            </flux:field>
            @if(!$canEdit)
                <div class="mt-2">
                    <flux:callout color="yellow">
                        Solo el <b>responsable del pago</b> puede editar residentes y mascotas.
                    </flux:callout>
                </div>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
        <!-- Mascotas -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="font-semibold">Mascotas</div>
                    <div class="text-sm text-gray-500">Podés cargar varias mascotas y su tipo.</div>
                </div>
                <flux:badge color="gray">{{ $pets->count() }}</flux:badge>
            </div>

            @if($pets->isEmpty())
                <div class="mt-3 text-sm text-gray-500">No hay mascotas cargadas.</div>
            @else
                <div class="mt-3 space-y-2">
                    @foreach($pets as $pet)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                            <div class="min-w-0">
                                <div class="font-medium">
                                    {{ $pet->type }}
                                    @if($pet->name)
                                        · {{ $pet->name }}
                                    @endif
                                </div>
                                @if($pet->notes)
                                    <div class="text-xs text-gray-500">{{ $pet->notes }}</div>
                                @endif
                            </div>

                            <flux:button
                                type="button"
                                size="sm"
                                variant="ghost"
                                color="red"
                                wire:click="removePet({{ $pet->id }})"
                                wire:confirm="¿Eliminar esta mascota?"
                                :disabled="!$canEdit"
                            >
                                Quitar
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                <div class="font-semibold">Agregar mascota</div>

                <form wire:submit="addPet" class="mt-3 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label>Tipo <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="petType" :disabled="!$canEdit">
                                <option value="">Seleccione</option>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Ave">Ave</option>
                                <option value="Pez">Pez</option>
                                <option value="Roedor">Roedor</option>
                                <option value="Reptil">Reptil</option>
                                <option value="Otro">Otro</option>
                            </flux:select>
                            <flux:error name="petType" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Nombre (opcional)</flux:label>
                            <flux:input wire:model="petName" placeholder="Ej: Rocky" :disabled="!$canEdit" />
                            <flux:error name="petName" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Notas (opcional)</flux:label>
                        <flux:input wire:model="petNotes" placeholder="Ej: tamaño mediano" :disabled="!$canEdit" />
                        <flux:error name="petNotes" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" :disabled="!$canEdit">
                        Agregar
                    </flux:button>
                </form>
            </div>
        </div>

        <!-- Alta rápida de residente -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="font-semibold">Agregar residente</div>
            <div class="text-sm text-gray-500">Cargá un habitante de la unidad.</div>

            <form wire:submit="addResident" class="mt-4 space-y-4">
                <flux:field>
                    <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="residentName" placeholder="Nombre y apellido" :disabled="!$canEdit" />
                    <flux:error name="residentName" />
                </flux:field>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Fecha de nacimiento</flux:label>
                        <flux:input type="date" wire:model="residentBirthDate" :disabled="!$canEdit" />
                        <flux:error name="residentBirthDate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Relación</flux:label>
                        <flux:select wire:model="residentRelationship" :disabled="!$canEdit">
                            <option value="">-</option>
                            <option value="Hijo/a">Hijo/a</option>
                            <option value="Cónyuge">Cónyuge</option>
                            <option value="Familiar">Familiar</option>
                            <option value="Otro">Otro</option>
                        </flux:select>
                        <flux:error name="residentRelationship" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Foto (opcional)</flux:label>
                    <div class="flex items-center gap-4">
                        @php
                            $previewUrl = null;
                            if ($residentPhoto) {
                                try {
                                    $previewUrl = $residentPhoto->temporaryUrl();
                                } catch (\Exception $e) {
                                    $previewUrl = null;
                                }
                            }

                            $initials = \Illuminate\Support\Str::of($residentName ?: 'Residente')
                                ->trim()
                                ->explode(' ')
                                ->filter()
                                ->take(2)
                                ->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))
                                ->implode('');
                        @endphp

                        @if($previewUrl)
                            <img src="{{ $previewUrl }}" alt="Vista previa" class="h-20 w-20 rounded-full object-cover" />
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-neutral-200 text-lg font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                {{ $initials }}
                            </div>
                        @endif

                        <div class="flex-1">
                            <flux:input wire:model="residentPhoto" type="file" accept="image/*" :disabled="!$canEdit" />
                            <flux:error name="residentPhoto" />
                            <flux:description>JPG/PNG hasta 2MB.</flux:description>
                            <div wire:loading wire:target="residentPhoto" class="mt-1 text-xs text-gray-500">Cargando imagen...</div>
                        </div>
                    </div>
                </flux:field>

                <flux:button type="submit" variant="primary" :disabled="!$canEdit">
                    Agregar
                </flux:button>
            </form>
        </div>
    </div>

    <!-- Listado de residentes -->
        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="font-semibold">Residentes actuales</div>
                    <div class="text-sm text-gray-500">Habitantes activos (sin fecha de fin).</div>
                </div>
                <flux:badge color="gray">{{ $residents->count() }}</flux:badge>
            </div>

            <div class="mt-4">
                @if($residents->isEmpty())
                    <div class="text-sm text-gray-500">No hay residentes cargados en esta unidad.</div>
                @else
                    <div class="space-y-2">
                        @foreach($residents as $r)
                            @php
                                $photoUrl = $r->profilePhotoUrl();
                                $initials = \Illuminate\Support\Str::of($r->name)
                                    ->trim()
                                    ->explode(' ')
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))
                                    ->implode('');
                            @endphp
                            <div class="flex items-center justify-between gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ $r->name }}" class="h-10 w-10 rounded-full object-cover flex-shrink-0" />
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-neutral-200 text-sm font-semibold text-black dark:bg-neutral-700 dark:text-white flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium truncate">{{ $r->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($r->birth_date)
                                                {{ $r->birth_date->format('d/m/Y') }}
                                                @if($r->isMinor())
                                                    · <span class="font-semibold">Menor</span>
                                                @endif
                                            @endif
                                            @if($r->relationship)
                                                {{ $r->birth_date ? '·' : '' }} {{ $r->relationship }}
                                            @endif
                                        </div>
                                        
                                        {{-- Email y estado de invitación --}}
                                        @if($r->canBeInvited() || $r->hasAuthAccount())
                                            <div class="mt-1 flex items-center gap-2">
                                                @if($editingResidentId === $r->id)
                                                    <flux:input 
                                                        wire:model="residentEmail" 
                                                        type="email" 
                                                        placeholder="email@ejemplo.com"
                                                        class="text-xs"
                                                    />
                                                    <flux:button 
                                                        type="button" 
                                                        size="xs" 
                                                        wire:click="saveResidentEmail"
                                                    >
                                                        Guardar
                                                    </flux:button>
                                                    <flux:button 
                                                        type="button" 
                                                        size="xs" 
                                                        variant="ghost"
                                                        wire:click="cancelEditEmail"
                                                    >
                                                        Cancelar
                                                    </flux:button>
                                                @else
                                                    @if($r->email)
                                                        <span class="text-xs text-gray-600 dark:text-gray-400">📧 {{ $r->email }}</span>
                                                    @else
                                                        <span class="text-xs text-gray-400">Sin email</span>
                                                    @endif
                                                    
                                                    @if($r->hasAuthAccount())
                                                        <flux:badge size="sm" color="green">✓ Tiene cuenta</flux:badge>
                                                    @elseif($r->invitation_sent_at)
                                                        <flux:badge size="sm" color="yellow">Invitado {{ $r->invitation_sent_at->diffForHumans() }}</flux:badge>
                                                    @endif
                                                @endif
                                            </div>
                                            @error('residentEmail')
                                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($r->canBeInvited() && $canEdit)
                                        @if(!$r->email)
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="editResidentEmail({{ $r->id }})"
                                            >
                                                Agregar email
                                            </flux:button>
                                        @else
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="primary"
                                                wire:click="sendInvitation({{ $r->id }})"
                                            >
                                                Enviar invitación
                                            </flux:button>
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="editResidentEmail({{ $r->id }})"
                                            >
                                                Editar
                                            </flux:button>
                                        @endif
                                    @endif
                                    
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        color="red"
                                        wire:click="finishResident({{ $r->id }})"
                                        wire:confirm="¿Finalizar este residente (dejará de estar activo)?"
                                        :disabled="!$canEdit"
                                    >
                                        Finalizar
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
