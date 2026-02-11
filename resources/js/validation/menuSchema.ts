import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';

// Zod schema for Menu form validation
export const menuSchema = z.object({
    name: z
        .string({ required_error: 'Menu name is required' })
        .min(1, 'Menu name is required')
        .max(255, 'Menu name must be less than 255 characters'),
    description: z
        .string()
        .max(1000, 'Description must be less than 1000 characters')
        .optional()
        .nullable(),
    image_url: z.string().optional().nullable(),
    status: z.boolean().default(true),
    schedule_mode: z.string().optional().nullable(),
    schedule_days: z.string().optional().nullable(),
    schedule_start_time: z.string().optional().nullable(),
    schedule_end_time: z.string().optional().nullable(),
    schedule_start_date: z.string().optional().nullable(),
    schedule_end_date: z.string().optional().nullable(),
    schedule_status: z.boolean().optional().nullable(),
});

// TypedSchema for vee-validate
export const menuValidationSchema = toTypedSchema(menuSchema);

// Type inference from Zod schema
export type MenuFormValues = z.infer<typeof menuSchema>;
