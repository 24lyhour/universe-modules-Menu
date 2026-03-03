import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';

// Zod schema for Category form validation
export const categorySchema = z.object({
    name: z
        .string({ required_error: 'Category name is required' })
        .min(1, 'Category name is required')
        .max(255, 'Category name must be less than 255 characters'),
    description: z
        .string()
        .max(1000, 'Description must be less than 1000 characters')
        .optional()
        .nullable(),
    menu_id: z
        .number({ required_error: 'Menu is required' })
        .int()
        .positive('Menu is required'),
    image_url: z.string().optional().nullable(),
    product_type: z.enum(['phone', 'computer', 'tablet', 'accessory', 'other'], {
        required_error: 'Product type is required',
    }),
    sort_order: z.number().min(0).default(0),
    status: z.boolean().default(true),
});

// TypedSchema for vee-validate
export const categoryValidationSchema = toTypedSchema(categorySchema);

// Type inference from Zod schema
export type CategoryFormValues = z.infer<typeof categorySchema>;
