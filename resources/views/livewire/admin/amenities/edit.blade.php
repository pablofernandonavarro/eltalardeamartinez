<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <flux:button href="{{ route('admin.amenities.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
                Volver
            </flux:button>
        </div>
        <flux:heading size="xl">Editar Amenidad</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Modifica los datos de la amenidad
        </p>
    </div>

    <form wire:submit="save" class="max-w-3xl">
        <div class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Pileta" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug (identificador) <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="slug" placeholder="Ej: pileta" />
                    <flux:error name="slug" />
                    <flux:description>Solo letras minúsculas, números y guiones</flux:description>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Descripción</flux:label>
                <flux:textarea wire:model="description" placeholder="Descripción breve" rows="2" />
                <flux:error name="description" />
            </flux:field>

            <div class="grid md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Color <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="icon_color">
                        <option value="blue">Azul</option>
                        <option value="orange">Naranja</option>
                        <option value="green">Verde</option>
                        <option value="purple">Morado</option>
                        <option value="red">Rojo</option>
                        <option value="amber">Ámbar</option>
                    </flux:select>
                    <flux:error name="icon_color" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo de horario</flux:label>
                    <flux:select wire:model="schedule_type">
                        <option value="">Seleccionar...</option>
                        <option value="weekdays_weekends">Weekdays/Weekends</option>
                        <option value="all_days">Todos los días</option>
                        <option value="weekdays">Solo weekdays</option>
                        <option value="by_reservation">Por reserva</option>
                        <option value="open_access">Acceso libre</option>
                    </flux:select>
                    <flux:error name="schedule_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Orden <span class="text-red-500">*</span></flux:label>
                    <flux:input type="number" wire:model="display_order" />
                    <flux:error name="display_order" />
                </flux:field>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Horario entre semana</flux:label>
                    <flux:input wire:model="weekday_schedule" placeholder="9:00-13:00,15:00-22:00" />
                    <flux:error name="weekday_schedule" />
                </flux:field>

                <flux:field>
                    <flux:label>Horario fin de semana</flux:label>
                    <flux:input wire:model="weekend_schedule" placeholder="10:00-20:00" />
                    <flux:error name="weekend_schedule" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Disponibilidad</flux:label>
                <flux:input wire:model="availability" placeholder="Temporada de Verano" />
                <flux:error name="availability" />
            </flux:field>

            <flux:field>
                <flux:label>Información adicional</flux:label>
                <flux:textarea wire:model="additional_info" rows="3" />
                <flux:error name="additional_info" />
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="is_active" label="Activo" />
            </flux:field>
        </div>

        <div class="mt-8 flex gap-3">
            <flux:button type="submit" variant="primary">
                Guardar cambios
            </flux:button>
            <flux:button href="{{ route('admin.amenities.index') }}" variant="ghost" wire:navigate>
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
